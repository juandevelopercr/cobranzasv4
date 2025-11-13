window.renderFusionCharts = function (chartData) {
  window.fusionChartInstances = window.fusionChartInstances || {};

  // Paleta de colores CORREGIDA (array de colores individuales)
  const themePalettes = {
    candy: [
      '#36B5D8',
      '#F0DC46',
      '#F066AC',
      '#6EC85A',
      '#6E80CA',
      '#E09653',
      '#E1D7AD',
      '#61C8C8',
      '#EBE4F4',
      '#E64141'
    ],
    carbon: [
      '#444444',
      '#666666',
      '#888888',
      '#aaaaaa',
      '#cccccc',
      '#555555',
      '#777777',
      '#999999',
      '#bbbbbb',
      '#dddddd'
    ],
    fint: [
      '#0075c2',
      '#1aaf5d',
      '#f2c500',
      '#f45b00',
      '#8e0000',
      '#0e948c',
      '#8cbb2c',
      '#f3de00',
      '#c02d00',
      '#5b0101'
    ],
    fusion: ['#5D62B5', '#29C3BE', '#F2726F', '#FFC533', '#62B58F', '#BC95DF', '#67CDF2'],
    gammel: [
      '#7CB5EC',
      '#434348',
      '#8EED7D',
      '#F7A35C',
      '#8085E9',
      '#F15C80',
      '#E4D354',
      '#2B908F',
      '#F45B5B',
      '#91E8E1'
    ],
    ocean: [
      '#04476c',
      '#4d998d',
      '#77be99',
      '#a7dca6',
      '#cef19a',
      '#0e948c',
      '#64ad93',
      '#8fcda0',
      '#bbe7a0',
      '#dcefc1'
    ],
    umber: [
      '#5D4037',
      '#7B1FA2',
      '#0288D1',
      '#388E3C',
      '#E64A19',
      '#0097A7',
      '#AFB42B',
      '#8D6E63',
      '#5D4037',
      '#795548'
    ],
    zune: ['#0075c2', '#1aaf5d', '#f2c500', '#f45b00', '#8e0000', '#0e948c', '#8cbb2c', '#f3de00', '#c02d00', '#5b0101']
  };

  // Función para obtener colores rotados
  function getRotatedColors(theme, rotationIndex = 0) {
    const palette = themePalettes[theme] || themePalettes.zune;
    const rotated = [...palette];

    // Rotar la paleta según el índice
    for (let i = 0; i < rotationIndex; i++) {
      rotated.push(rotated.shift());
    }
    console.log(rotated);
    return rotated;
  }

  //*****************************************************************************************************//
  //************************** Gráfico de pastel formalizaciones producto Mes USD************************//
  //*****************************************************************************************************//

  const pieFormalizacionProductMesUSDContainerId = 'formalizaciones_product_mes_usd';
  const pieformalizacionProductMesUSDContainer = document.getElementById(pieFormalizacionProductMesUSDContainerId);

  if (pieformalizacionProductMesUSDContainer) {
    const validData = chartData.pie_formalizaciones_product_mes_usd?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataFormalizacionProductMesUSDPie = validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataFormalizacionProductMesUSDPie) {
      pieformalizacionProductMesUSDContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.pie_formalizaciones_product_mes_usd?.caption || ''}
                ${chartData.pie_formalizaciones_product_mes_usd?.subCaption ? '| ' + chartData.pie_formalizaciones_product_mes_usd.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[pieFormalizacionProductMesUSDContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductMesUSDContainerId].dispose();
        delete window.fusionChartInstances[pieFormalizacionProductMesUSDContainerId];
      }
    } else {
      if (window.fusionChartInstances[pieFormalizacionProductMesUSDContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductMesUSDContainerId].dispose();
      }

      pieformalizacionProductMesUSDContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'pie3d',
          renderAt: pieFormalizacionProductMesUSDContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.pie_formalizaciones_product_mes_usd.caption || 'Formalizaciones Products en USD',
              subCaption: chartData.pie_formalizaciones_product_mes_usd.subCaption || '',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 0).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '0',
              forceDecimals: '1',
              exportEnabled: '1',
              enableSmartLabels: '1',
              centerLabel: '$label: $value',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '13',
              legendItemFontSize: '14',
              legendCaptionFontSize: '16',
              valueFontSize: '12',
              outCnvBaseFontSize: '14',
              labelDisplay: 'WRAP',
              labelPadding: '5'
            },
            data: validData
          }
        };

        window.fusionChartInstances[pieFormalizacionProductMesUSDContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[pieFormalizacionProductMesUSDContainerId].render();
      } catch (error) {
        pieformalizacionProductMesUSDContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //***************************** Gráfico de pastel formalizaciones Productos Mes CRC**********************//
  //*******************************************************************************************************//

  const pieFormalizacionProductMesCRCContainerId = 'formalizaciones_product_mes_crc';
  const pieformalizacionProductMesCRCContainer = document.getElementById(pieFormalizacionProductMesCRCContainerId);

  if (pieformalizacionProductMesCRCContainer) {
    const validData = chartData.pie_formalizaciones_product_mes_crc?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataFormalizacionProductMesCRCPie = validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataFormalizacionProductMesCRCPie) {
      pieformalizacionProductMesCRCContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.pie_formalizaciones_product_mes_crc?.caption || ''}
                ${chartData.pie_formalizaciones_product_mes_crc?.subCaption ? '| ' + chartData.pie_formalizaciones_product_mes_crc.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[pieFormalizacionProductMesCRCContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductMesCRCContainerId].dispose();
        delete window.fusionChartInstances[pieFormalizacionProductMesCRCContainerId];
      }
    } else {
      if (window.fusionChartInstances[pieFormalizacionProductMesCRCContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductMesCRCContainerId].dispose();
      }

      pieformalizacionProductMesCRCContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'pie3d',
          renderAt: pieFormalizacionProductMesCRCContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.pie_formalizaciones_product_mes_crc.caption || 'Formalizaciones Productos en CRC',
              subCaption: chartData.pie_formalizaciones_product_mes_crc.subCaption || '',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 5).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '0',
              forceDecimals: '1',
              exportEnabled: '1',
              enableSmartLabels: '1',
              centerLabel: '$label: $value',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '13',
              legendItemFontSize: '14',
              legendCaptionFontSize: '16',
              valueFontSize: '12',
              outCnvBaseFontSize: '14',
              labelDisplay: 'WRAP',
              labelPadding: '5'
            },
            data: validData
          }
        };

        window.fusionChartInstances[pieFormalizacionProductMesCRCContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[pieFormalizacionProductMesCRCContainerId].render();
      } catch (error) {
        pieformalizacionProductMesCRCContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //************************* Gráfico de pastel formalizaciones Productos Year USD*************************//
  //*******************************************************************************************************//

  const pieFormalizacionProductYearUSDContainerId = 'formalizaciones_product_year_usd';
  const pieformalizacionProductYearUSDContainer = document.getElementById(pieFormalizacionProductYearUSDContainerId);

  if (pieformalizacionProductYearUSDContainer) {
    const validData = chartData.pie_formalizaciones_product_year_usd?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataFormalizacionProductYearUSDPie =
      validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataFormalizacionProductYearUSDPie) {
      pieformalizacionProductYearUSDContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.pie_formalizaciones_product_year_usd?.caption || ''}
                ${chartData.pie_formalizaciones_product_year_usd?.subCaption ? '| ' + chartData.pie_formalizaciones_product_year_usd.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[pieFormalizacionProductYearUSDContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductYearUSDContainerId].dispose();
        delete window.fusionChartInstances[pieFormalizacionProductYearUSDContainerId];
      }
    } else {
      if (window.fusionChartInstances[pieFormalizacionProductYearUSDContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductYearUSDContainerId].dispose();
      }

      pieformalizacionProductYearUSDContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'pie3d',
          renderAt: pieFormalizacionProductYearUSDContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.pie_formalizaciones_product_year_usd.caption || 'Formalizaciones Productos en USD',
              subCaption: chartData.pie_formalizaciones_product_year_usd.subCaption || '',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 0).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '0',
              forceDecimals: '1',
              exportEnabled: '1',
              enableSmartLabels: '1',
              centerLabel: '$label: $value',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '13',
              legendItemFontSize: '14',
              legendCaptionFontSize: '16',
              valueFontSize: '12',
              outCnvBaseFontSize: '14',
              labelDisplay: 'WRAP',
              labelPadding: '5'
            },
            data: validData
          }
        };

        window.fusionChartInstances[pieFormalizacionProductYearUSDContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[pieFormalizacionProductYearUSDContainerId].render();
      } catch (error) {
        pieformalizacionProductYearUSDContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //*************************** Gráfico de pastel formalizaciones Productos Year CRC***********************//
  //*******************************************************************************************************//

  const pieFormalizacionProductYearCRCContainerId = 'formalizaciones_product_year_crc';
  const pieformalizacionProductYearCRCContainer = document.getElementById(pieFormalizacionProductYearCRCContainerId);

  if (pieformalizacionProductYearCRCContainer) {
    const validData = chartData.pie_formalizaciones_product_year_crc?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataFormalizacionProductYearCRCPie =
      validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataFormalizacionProductYearCRCPie) {
      pieformalizacionProductYearCRCContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.pie_formalizaciones_product_year_crc?.caption || ''}
                ${chartData.pie_formalizaciones_product_year_crc?.subCaption ? '| ' + chartData.pie_formalizaciones_product_year_crc.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[pieFormalizacionProductYearCRCContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductYearCRCContainerId].dispose();
        delete window.fusionChartInstances[pieFormalizacionProductYearCRCContainerId];
      }
    } else {
      if (window.fusionChartInstances[pieFormalizacionProductYearCRCContainerId]) {
        window.fusionChartInstances[pieFormalizacionProductYearCRCContainerId].dispose();
      }

      pieformalizacionProductYearCRCContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'pie3d',
          renderAt: pieFormalizacionProductYearCRCContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.pie_formalizaciones_product_year_crc.caption || 'Formalizaciones Productos en CRC',
              subCaption: chartData.pie_formalizaciones_product_year_crc.subCaption || '',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 2).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '0',
              forceDecimals: '1',
              exportEnabled: '1',
              enableSmartLabels: '1',
              centerLabel: '$label: $value',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '13',
              legendItemFontSize: '14',
              legendCaptionFontSize: '16',
              valueFontSize: '12',
              outCnvBaseFontSize: '14',
              labelDisplay: 'WRAP',
              labelPadding: '5'
            },
            data: validData
          }
        };

        window.fusionChartInstances[pieFormalizacionProductYearCRCContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[pieFormalizacionProductYearCRCContainerId].render();
      } catch (error) {
        pieformalizacionProductYearCRCContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }
};

// Redimensionar con debounce
let resizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    if (window.fusionChartInstances) {
      for (const containerId in window.fusionChartInstances) {
        if (window.fusionChartInstances[containerId]) {
          try {
            window.fusionChartInstances[containerId].resizeTo({
              width: '100%',
              height: '500'
            });
          } catch (error) {
            console.error(`Error al redimensionar ${containerId}:`, error);
          }
        }
      }
    }
  }, 250);
});
