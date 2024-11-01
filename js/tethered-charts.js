/**
 * Tethered's Chart
 */

class TetheredCharts {
  constructor(height = false, width = false){

      if(height != false){
        this.chartHeight = height;
      }

      if(width != false){
        this.chartWidth = width + 10;
      }
    
      this.refresh();
  }

  refresh(){
      const charts = jQuery('[data-chart]');
      for(let chart of charts){
          this.update(chart);
      }
  }

  update(chart){
      const element = chart;

      chart = jQuery(element);
      if(!chart.attr('data-chart-init')){
          /* This chart has not been initialized */
          element._chartData = {
              datasets : chart.attr('data-sets'),
              aggregator : chart.attr('data-aggregator'),
              label : chart.attr('data-label'),
              height : chart.attr('data-height'),
              strokeColor : chart.attr('data-stroke-color'),
              gradientColor : chart.attr('data-gradient-color'),
              suffix : chart.attr('data-suffix'),
              boundaryBottom : chart.attr('data-boundary-bottom'), 
              boundaryTop : chart.attr('data-boundary-top'), 
              grandientRangeMax : chart.attr('data-gradient-range-max'),
              grandientRangeMode : chart.attr('data-gradient-range-mode')
          };

          if(element._chartData.datasets){
              try {
                  element._chartData.datasets = JSON.parse(element._chartData.datasets);
              } catch(ex){
                  element._chartData.datasets = false;
              }
          }

          this.render(element);
      }
  }

  render(element){
      const options = this.getOptions(element);

      if(options){
          const chart = new ApexCharts(element, options);
          element._chart = chart;

          chart.render();

          jQuery(element).data('chart-init', 'true');
      }
  }

  getOptions(element){
      if(element._chartData.datasets){
          const options = {
              chart : {
                  type : 'line',
                  toolbar : { 
                      tools : {
                          zoom : false,
                          pan : false,
                          zoomin : false,
                          zoomout : false,
                          download : false,
                          reset : false 
                      }
                  },
                  height : element._chartData.height || (this.chartHeight + "px"),
                  width : element._chartData.width || (this.chartWidth + "px"),
                  parentHeightOffset : 0,
                  animations : { enabled : false },
                  sparkline : {enabled : true}
              },
              legend : { show : false },
              stroke : { width : 2, curve : 'straight' },
              grid : { show : false },
              xaxis : {
                  labels : { show : false },
                  axisTicks : { show : false },
                  axisBorder : { show : false }
              },
              yaxis : {
                  min : parseInt(element._chartData.boundaryBottom) || -20,
                  max : parseInt(element._chartData.boundaryTop) || 120,
                  labels : { show : false },
                  axisTicks : { show : false },
                  axisBorder : { show : false }
              },
              tooltip : {
                  x : {
                      formatter : (value, config) => {
                          /* Convert to the date */
                          let label = "";
                          let dynamic = element._chartData.datasets[0][config.dataPointIndex];
                          if(typeof dynamic['jstime'] !== 'undefined'){
                              label = new Date(dynamic['jstime']).toLocaleDateString('en-us', { day: "numeric", month:"short", hour: "2-digit", minute: "2-digit" });
                          }  

                          return label;
                      }
                  }, 
                  y : {
                      formatter : (value, config) => {
                          /* Convert to the date */
                          let label = "";
                          let dynamic = element._chartData.datasets[0][config.dataPointIndex];
                          if(typeof dynamic[element._chartData.aggregator] !== 'undefined'){
                              label = dynamic[element._chartData.aggregator].toFixed(2) + (element._chartData.suffix || "%");
                          }

                          return label;
                      },
                      title : {
                          formatter : (value, config) => {
                              return (element._chartData.label || "Uptime") + ":";
                          }
                      }
                  },
                  marker: {
                      show: false,
                  }
              }
          };

          const info = {
              min : Infinity,
              max : 0
          };

          /* Default series */
          options.series = [];
          for(let dataset of element._chartData.datasets){
              const compiled = {
                  data : []
              };

              if(dataset instanceof Array || dataset instanceof Object){
                  for(let i in dataset){
                      const data = dataset[i];
                      let sample = 0;
                      if(typeof data[element._chartData.aggregator] !== 'undefined'){
                          sample = data[element._chartData.aggregator];
                      } else if(typeof data['value'] !== 'undefined'){
                          sample = data['value'];
                      }

                      compiled.data.push(sample);

                      if(sample < info.min){
                          info.min = sample;
                      }

                      if(sample > info.max){
                          info.max = sample;
                      }
                  }
              } else {
                  compiled.data.push(dataset);
              }

              options.series.push(compiled);
          }

          info.delta = info.max - info.min;

          if(element._chartData.boundaryTop === 'auto'){
              options.yaxis.max = info.max + (info.max * 0.2);
          }

          if(element._chartData.boundaryBottom === 'auto'){
              options.yaxis.min = info.min - (info.max * 0.2);
          }

          const colorRange = {
              top : (element._chartData.strokeColor || '#10b981'),
              bottom : (element._chartData.gradientColor || '#f43f5e')
          };

          let colorLerpRangeMax = element._chartData.grandientRangeMax ? parseInt(element._chartData.grandientRangeMax) : 100;
          if(element._chartData.grandientRangeMode && element._chartData.grandientRangeMode === 'auto'){
              if(info.max < colorLerpRangeMax && info.delta > (colorLerpRangeMax / 4)){
                  colorLerpRangeMax = info.max + (info.max);
              }
          }

          if(info.min === info.max){
              /* Gradients will misbehave, so we neeed to flatten them */
              colorRange.top = this.lerpColor(colorRange.bottom, colorRange.top, info.max / colorLerpRangeMax);
          } else {
              colorRange.top = this.lerpColor(colorRange.bottom, colorRange.top, info.max / colorLerpRangeMax);
              colorRange.bottom = this.lerpColor(colorRange.bottom, colorRange.top, info.min / colorLerpRangeMax);

              /* Safe to add gradient because there will be a delta value */
              options.fill = {
                  type: 'gradient',
                  gradient: {
                      shade: 'dark',
                      gradientToColors: [colorRange.bottom],
                      shadeIntensity: 1,
                      type: 'vertical',
                      inverseColors : false,
                      opacityFrom: 1,
                      opacityTo: 1,
                      stops: [0, 100]
                  },
              };
          }

          options.colors = [colorRange.top];

          options.markers = {
              strokeColors : "#27272a",
              colors: ["#fff"]
          };
          
          return options;
      }
      return false;
  }

  /**
   * Lerp between to colors by a percentage
   * 
   * @param string hexA Low hex, with hash
   * @param string hexB High hex, with hash
   * @param number percent Percentage to lerp by
   * 
   * @returns string
   */
  lerpColor(hexA, hexB, percent) {
      percent = percent > 1 ? percent / 100 : percent;
      const rgb = {
          a : this.hexToRgb(hexA),
          b : this.hexToRgb(hexB)
      };

      rgb.c = {
          r : Math.round(rgb.a.r + (rgb.b.r - rgb.a.r) * percent),
          g : Math.round(rgb.a.g + (rgb.b.g - rgb.a.g) * percent),
          b : Math.round(rgb.a.b + (rgb.b.b - rgb.a.b) * percent),
      }
    
      return this.rgbToHex(rgb.c);
  }

  /**
   * Convert a hex to an RGB object
   * 
   * @param string hex The hex to convert, including it's hash
   * 
   * @returns object
   */
  hexToRgb(hex){
      return {
          r : parseInt(hex.substring(1, 3), 16),
          g : parseInt(hex.substring(3, 5), 16),
          b : parseInt(hex.substring(5, 7), 16)
      }
  }

  /**
   * Convert an RGB object to a hex 
   * 
   * @param object rgb The RGB object
   * 
   * @returns string
   */
  rgbToHex(rgb){
      return "#" + ((1 << 24) + (rgb.r << 16) + (rgb.g << 8) + rgb.b).toString(16).slice(1);
  }
}