import ApexCharts from 'apexcharts';

// code used in wordpress/wp-content/themes/base/Account/PromoteView.twig for navigating statistics
const changeDataSetButtons = document.querySelectorAll('.btnChangeDataSet');
changeDataSetButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    changeDataSetButtons.forEach((changeDataSetButton) => {
      changeDataSetButton.classList.remove('active');
    });

    button.classList.add('active');

    const dataType = button.getAttribute('data-type');
    if (dataType) {
      const elements = document.querySelectorAll(
        '.promoteStatisticTable .promoteStatisticTableValue[data-type]',
      );
      elements.forEach((element) => {
        if (element.getAttribute('data-type') === dataType) {
          element.style.display = 'block';
        } else {
          element.style.display = 'none';
        }
      });
    }
  });
});

// Earnings chart
const options = {
  series: [
    {
      name: promoteChartData[0].name,
      data: promoteChartData[0].data,
    },
    {
      name: promoteChartData[1].name,
      data: promoteChartData[1].data,
    },
  ],
  chart: {
    type: 'area',
    height: '280px',
    fontFamily: 'inherit',
    toolbar: {
      show: false,
    },
  },
  dataLabels: {
    enabled: false,
  },
  xaxis: {
    categories: promoteChartData[0].categories,
    tickAmount: 6,
    labels: {
      style: {
        colors: '#99a1b7',
        fontSize: '12px',
      },
    },
  },
  yaxis: {
    tickAmount: 5,
    labels: {
      formatter: function (value) {
        return value.toFixed(2);
      },
      style: {
        colors: '#99a1b7',
        fontSize: '12px',
      },
    },
  },
  stroke: {
    curve: 'smooth',
    show: true,
    width: 3,
    colors: ['#50cd89', '#1b84ff'],
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.2,
    },
  },
  states: {
    normal: {
      filter: {
        type: 'none',
        value: 0,
      },
    },
    hover: {
      filter: {
        type: 'none',
        value: 0,
      },
    },
    active: {
      allowMultipleDataPointsSelection: false,
      filter: {
        type: 'none',
        value: 0,
      },
    },
  },
  tooltip: {
    style: {
      fontSize: '12px',
    },
  },
  colors: ['#50cd89', '#1b84ff'],
  grid: {
    borderColor: '#78829D',
    strokeDashArray: 4,
    yaxis: {
      lines: {
        show: true,
      },
    },
  },
};

const chart = new ApexCharts(document.querySelector('#promoteChart'), options);
chart.render();

function copyToClipboard() {
  const btns = document.querySelectorAll('.js-copy-to-clipboard');

  btns.forEach(function (e) {
    e.addEventListener('click', function (e) {
      const targetId = e.target.closest('.btn').dataset.copyTarget;
      const input = document.getElementById(targetId);

      input.select();
      input.setSelectionRange(0, 99999);

      navigator.clipboard.writeText(input.value);
    });
  });
}
copyToClipboard();
