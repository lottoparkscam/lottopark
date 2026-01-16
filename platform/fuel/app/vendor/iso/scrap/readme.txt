----------------------------------------------------
This one works with PhantomJS
It crawls the ISO site and downloads the data
So we can use it on our site almost fully localized
Author: icetique 2016/12/22
----------------------------------------------------

Run with `phantomjs scrapiso.js`.

`countries.json` and `subdivisions.json` are the results.
Use the dev/subdivisions action to prepare it to use with our system,
it will create ready to use files inside `iso` folder.
`countries.json` is used as is to filter CLDR list of countries to not include
such territories like: `European Union` as they are not proper countries.