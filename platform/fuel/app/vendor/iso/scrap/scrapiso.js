var page = require('webpage').create();

var loadInterval = -1;
var countries;
var subdivisions = new Array();


var fs = require('fs');


page.viewportSize = { width: 1366, height: 768 };

var page_settings = { encoding: "utf8" };

console.log('Loading page...');

page.open('https://www.iso.org/obp/ui/#search/code/', page_settings, function(status) {
	console.log("Status: " + status);
	console.log('Injecting jQuery...');
	page.includeJs("https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js", function() {
		
		if(status === "success") {
		  	console.log("Page loaded. Loading data...");
		  	loadInterval = setInterval(checkDataLoad, 1000);
		}
	});
});

// wait for main page load
function checkDataLoad() {
	console.log("Checking if AJAX data has been loaded...");
	var tables = page.evaluate(function() {
		return $('.v-table-table').length;
	});
	if(tables > 0)
	{
		console.log("Data loaded! Changing results per page to 300...");
		clearInterval(loadInterval);
		page.evaluate(function() {

			var select = document.querySelector('.v-select-select');
			select.selectedIndex = 7;

			// this one works only with old jqueries, so need the snippet below
			// $('.v-select-select').val("8").change();

			var evt = document.createEvent("UIEvents"); // or "HTMLEvents"
	        evt.initUIEvent("change", true, true);
	        select.dispatchEvent(evt);
		});
		loadInterval = setInterval(checkDataLoad2, 3000);
		
	}
	else
	{
		console.log("Data still not loaded... Waiting...");
	}
}

// wait for full-results load (300 per page)
function checkDataLoad2()
{
	console.log('Checking if results have downloaded...');
	var pages = page.evaluate(function() {
		return $('.i-paging').length;
	});
	if(pages == 0)
	{
		console.log('Results downloaded! Scrapping list of countries...');
		clearInterval(loadInterval);
		countries = page.evaluate(function() {
			var data = new Array();
			$('.v-table-table tr').each(function(index) {
				var tds = $(this).find('td');
				data[index] = tds.eq(2).text();
			});
			return data;
		});
		console.log('Scrapped list of countries [' + countries.length + ']: ' + countries);
		page.close();

		var path = 'countries.json';
		var content = JSON.stringify(countries);
		fs.write(path, content, 'w');

		mainQuery();
	}
	else
	{
		console.log('Results still not loaded... Waiting...');
	}
}

var act_country = 0;
// iterate
function mainQuery()
{
	if(act_country < countries.length)
	{
		console.log("\n");
		console.log('Downloading metadata for country ['+(act_country+1)+'/'+countries.length+']: ' + countries[act_country]);

		page = require('webpage').create();
		page.viewportSize = { width: 1366, height: 768 };

		page.open('https://www.iso.org/obp/ui/#iso:code:3166:'+countries[act_country], page_settings, function(status) {
			console.log("Status: " + status);
			console.log('Injecting jQuery...');
			page.includeJs("https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js", function() {
				if(status === "success") {
				  	console.log("Page loaded. Loading data...");
				  	loadInterval = setInterval(checkDataLoad3, 2000);
				}
			});
		});
	}
	else
	{
		var path = 'subdivisions.json';
		var content = JSON.stringify(subdivisions);

		fs.write(path, content, 'w');

		//page.render('example.png');
		phantom.exit();
	}
}

// check if specified country page has been loaded
function checkDataLoad3()
{
	console.log("Checking if country " + countries[act_country] + " has been loaded...");
	var header = page.evaluate(function() {
		return $('.core-view-header').length;
	});
	if(header > 0)
	{
		console.log("Country loaded! Scrapping country data...");
		clearInterval(loadInterval);
		var item_subtypes = page.evaluate(function() {
			var subtypes = new Array();
			var i = 0;
			$('#country-subdivisions > p').each(function() {
				var locales = $(this).find(".category-locales");
				if(locales.length)
				{
					subtypes[i] = new Array();
					locales.each(function(index) {
						subtypes[i].push(locales.eq(index).text());
					});
					i++;
				}
			});
			return subtypes;
		});
		console.log("Subtypes: " + item_subtypes);

		var item_divisions = page.evaluate(function() {
			var divisions = new Array();
			$('#country-subdivisions table tbody tr').each(function() {
				var tds = $(this).find('td');
				//subdivision category, code, name, lang code, parent, local variant
				divisions.push([tds.eq(0).text(), tds.eq(1).text(), tds.eq(2).text(), tds.eq(4).text(), tds.eq(6).text(), tds.eq(3).text()]);
			});
			return divisions;
		});

		console.log("Subdivisions: " + item_divisions);

		var item_lastupdate = page.evaluate(function() {
			return $('table').last().find('tbody tr').eq(0).find('td').eq(0).text();
		});

		console.log("Last update: " + item_lastupdate);

		subdivisions.push([countries[act_country], item_subtypes, item_divisions, item_lastupdate]);

		console.log("Country " + countries[act_country] + " scrapped!");
		page.close();
		act_country++;
		mainQuery();
		
	}
	else
	{
		console.log("Data still not loaded... Waiting...");
	}
}