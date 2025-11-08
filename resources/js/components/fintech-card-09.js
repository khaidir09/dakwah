// Import Chart.js
import {
  Chart, PieController, ArcElement, TimeScale, Tooltip,
} from 'chart.js';
import 'chartjs-adapter-moment';

import { getCssVariable } from '../utils';
Chart.register(PieController, ArcElement, TimeScale, Tooltip);

// A chart built with Chart.js 3
// https://www.chartjs.org/
const fintechCard09 = () => {
  const ctx = document.getElementById('fintech-card-09');
  if (!ctx) return;

  const darkMode = localStorage.getItem('dark-mode') === 'true';

  const tooltipTitleColor = {
    light: '#1F2937',
    dark: '#F3F4F6'
  };

  const tooltipBodyColor = {
    light: '#6B7280',
    dark: '#9CA3AF'
  };

  const tooltipBgColor = {
    light: '#ffffff',
    dark: '#374151'
  };

  const tooltipBorderColor = {
    light: '#E5E7EB',
    dark: '#4B5563'
  };  

  fetch('/json-data-feed?datatype=22')
    .then(a => {
      return a.json();
    })
    .then(result => {

      const chart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: result.labels,
          datasets: [
            {
              label: 'Sessions By Device',
              data: result.data,
              backgroundColor: [
                getCssVariable('--color-green-400'),
                getCssVariable('--color-yellow-400'),
                getCssVariable('--color-sky-500'),
                getCssVariable('--color-violet-500'),
              ],
              hoverBackgroundColor: [
                getCssVariable('--color-green-500'),
                getCssVariable('--color-yellow-500'),
                getCssVariable('--color-sky-600'),
                getCssVariable('--color-violet-600'),
              ],
              borderWidth: 0,
            },
          ],
        },
        options: {
          layout: {
            padding: {
              top: 4,
              bottom: 4,
              left: 24,
              right: 24,
            },
          },
          plugins: {
            legend: {
              display: false,
            },
            tooltip: {
              titleColor: darkMode ? tooltipTitleColor.dark : tooltipTitleColor.light,
              bodyColor: darkMode ? tooltipBodyColor.dark : tooltipBodyColor.light,
              backgroundColor: darkMode ? tooltipBgColor.dark : tooltipBgColor.light,
              borderColor: darkMode ? tooltipBorderColor.dark : tooltipBorderColor.light,
            }, 
            htmlLegend: {
              // ID of the container to put the legend in
              containerID: 'fintech-card-09-legend',
            },
          },
          interaction: {
            intersect: false,
            mode: 'nearest',
          },
          animation: {
            duration: 200,
          },
          maintainAspectRatio: false,
        },
        plugins: [{
          id: 'htmlLegend',
          afterUpdate(c, args, options) {
            const legendContainer = document.getElementById(options.containerID);
            const ul = legendContainer.querySelector('ul');
            if (!ul) return;
            // Remove old legend items
            while (ul.firstChild) {
              ul.firstChild.remove();
            }
            // Reuse the built-in legendItems generator
            const items = c.options.plugins.legend.labels.generateLabels(c);
            items.forEach((item) => {
              const li = document.createElement('li');
              li.style.margin = '6px';
              // Button element
              const button = document.createElement('button');
              button.style.display = 'inline-flex';
              button.style.alignItems = 'center';
              button.style.opacity = item.hidden ? '.3' : '';
              button.onclick = () => {
                c.toggleDataVisibility(item.index, !item.index);
                c.update();
              };
              // Color box
              const box = document.createElement('span');
              box.style.display = 'block';
              box.style.width = '12px';
              box.style.height = '12px';
              box.style.borderRadius = 'calc(infinity * 1px)';
              box.style.marginRight = '6px';
              box.style.borderWidth = '3px';
              box.style.borderColor = item.fillStyle;
              box.style.pointerEvents = 'none';
              // Label
              const label = document.createElement('span');
              label.classList.add('text-gray-500', 'dark:text-gray-400');
              label.style.fontSize = '14px';
              label.style.lineHeight = 'calc(1.25 / 0.875)';
              const labelText = document.createTextNode(item.text);
              label.appendChild(labelText);
              li.appendChild(button);
              button.appendChild(box);
              button.appendChild(label);
              ul.appendChild(li);
            });
          },
        }],
      });
      
      document.addEventListener('darkMode', (e) => {
        const { mode } = e.detail;
        if (mode === 'on') {
          chart.options.plugins.tooltip.titleColor = tooltipTitleColor.dark;
          chart.options.plugins.tooltip.bodyColor = tooltipBodyColor.dark;
          chart.options.plugins.tooltip.backgroundColor = tooltipBgColor.dark;
          chart.options.plugins.tooltip.borderColor = tooltipBorderColor.dark;
        } else {
          chart.options.plugins.tooltip.titleColor = tooltipTitleColor.light;
          chart.options.plugins.tooltip.bodyColor = tooltipBodyColor.light;
          chart.options.plugins.tooltip.backgroundColor = tooltipBgColor.light;
          chart.options.plugins.tooltip.borderColor = tooltipBorderColor.light;
        }
        chart.update('none');
      });
    });
};

export default fintechCard09;
