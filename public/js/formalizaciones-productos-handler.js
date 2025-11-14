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

  //*******************************************************************************************************//
  //******************************** Gráfico de Heatmap Product USD ***************************************//
  //*******************************************************************************************************//

  const heatmapProductUsdContainerId = 'formalizaciones_product_usd_heatmap';
  const heatmapProductUsdContainer = document.getElementById(heatmapProductUsdContainerId);

  if (heatmapProductUsdContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapUsd = chartData.heatmap_product_usd?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapUsd) {
      heatmapProductUsdContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.heatmap_product_usd?.caption || ''}
                ${chartData.heatmap_product_usd?.subCaption ? '| ' + chartData.heatmap_product_usd.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[heatmapProductUsdContainerId]) {
        window.fusionChartInstances[heatmapProductUsdContainerId].dispose();
        delete window.fusionChartInstances[heatmapProductUsdContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapProductUsdContainerId]) {
        window.fusionChartInstances[heatmapProductUsdContainerId].dispose();
      }

      heatmapProductUsdContainer.innerHTML = `
            <div class="chart-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando gráfico...</span>
                </div>
                <p>Cargando visualización...</p>
            </div>`;

      try {
        // Dentro del try del heatmap
        const chartConfig = {
          type: 'heatmap',
          renderAt: heatmapProductUsdContainerId,
          width: '100%',
          height: '800', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_product_usd.caption || '-',
              subCaption: chartData.heatmap_product_usd.subCaption || '',
              theme: chartData.theme || 'zune',
              valueFontSize: '12',
              showLabels: '1',
              showValues: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> facturado en <b>$rowlabel</b>',
              valueBgAlpha: '40',

              // Configuración clave para mostrar nombres de meses
              xAxisName: 'Meses',
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',
              labelDisplay: 'WRAP',
              labelWrap: '1',
              labelMaxWidth: '60',
              useEllipsesWhenOverflow: '1',
              rotateLabels: '0', // No rotar etiquetas

              // Control de tamaño de celdas y espacios

              cellHeight: '30',
              cellWidth: '50',
              cellPadding: '5',
              plotSpacePercent: '50', // Más espacio para etiquetas
              canvasPadding: '30',
              canvasTopPadding: '40', // Espacio extra arriba para meses

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '$',
              decimals: '2',
              forceDecimals: '1',

              // Configuraciones de estilo
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              labelFontSize: '13',
              legendItemFontSize: '14',
              outCnvBaseFontSize: '14',
              labelPadding: '10', // Más espacio entre etiquetas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              // Mejorar visualización de valores
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90'
            },
            rows: chartData.heatmap_product_usd.rows || { row: [] },
            columns: chartData.heatmap_product_usd.columns || { column: [] },
            dataset: chartData.heatmap_product_usd.dataset || [],
            colorrange: chartData.heatmap_product_usd.colorrange || {
              gradient: '1',
              minvalue: '0',
              code: '#FCFBFF',
              color: [
                { code: '#FBE1EA', minvalue: '0', maxvalue: '10' },
                { code: '#FEB0BA', minvalue: '10', maxvalue: '20' },
                { code: '#f7f8fd', minvalue: '20', maxvalue: '30' },
                { code: '#DCE8F4', minvalue: '30', maxvalue: '40' },
                { code: '#6B96CB', minvalue: '40', maxvalue: '50' }
              ]
            }
          }
        };

        window.fusionChartInstances[heatmapProductUsdContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapProductUsdContainerId].render();
      } catch (error) {
        heatmapProductUsdContainer.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <h3>Error en el gráfico</h3>
                    <p>${error.message || 'Por favor intenta nuevamente'}</p>
                </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //******************************** Gráfico de Heatmap Product CRC ******************************************//
  //*******************************************************************************************************//

  const heatmapProductCrcContainerId = 'formalizaciones_producto_crc_heatmap';
  const heatmapProductCrcContainer = document.getElementById(heatmapProductCrcContainerId);

  if (heatmapProductCrcContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapCrc = chartData.heatmap_product_crc?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapCrc) {
      heatmapProductCrcContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.heatmap_product_crc?.caption || ''}
                ${chartData.heatmap_product_crc?.subCaption ? '| ' + chartData.heatmap_product_crc.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[heatmapProductCrcContainerId]) {
        window.fusionChartInstances[heatmapProductCrcContainerId].dispose();
        delete window.fusionChartInstances[heatmapProductCrcContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapProductCrcContainerId]) {
        window.fusionChartInstances[heatmapProductCrcContainerId].dispose();
      }

      heatmapProductCrcContainer.innerHTML = `
            <div class="chart-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando gráfico...</span>
                </div>
                <p>Cargando visualización...</p>
            </div>`;

      try {
        // Dentro del try del heatmap
        const chartConfig = {
          type: 'heatmap',
          renderAt: heatmapProductCrcContainerId,
          width: '100%',
          height: '800', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_product_crc.caption || '-',
              subCaption: chartData.heatmap_product_crc.subCaption || '',
              theme: chartData.theme || 'zune',
              valueFontSize: '12',
              showLabels: '1',
              showValues: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> facturado en <b>$rowlabel</b>',
              valueBgAlpha: '40',

              // Configuración clave para mostrar nombres de meses
              xAxisName: 'Meses',
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',
              labelDisplay: 'WRAP',
              labelWrap: '1',
              labelMaxWidth: '60',
              useEllipsesWhenOverflow: '1',
              rotateLabels: '0', // No rotar etiquetas

              // Control de tamaño de celdas y espacios

              cellHeight: '30',
              cellWidth: '50',
              cellPadding: '5',
              plotSpacePercent: '50', // Más espacio para etiquetas
              canvasPadding: '30',
              canvasTopPadding: '40', // Espacio extra arriba para meses

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '$',
              decimals: '2',
              forceDecimals: '1',

              // Configuraciones de estilo
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              labelFontSize: '13',
              legendItemFontSize: '14',
              outCnvBaseFontSize: '14',
              labelPadding: '10', // Más espacio entre etiquetas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              // Mejorar visualización de valores
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90'
            },
            rows: chartData.heatmap_product_crc.rows || { row: [] },
            columns: chartData.heatmap_product_crc.columns || { column: [] },
            dataset: chartData.heatmap_product_crc.dataset || [],
            colorrange: chartData.heatmap_product_crc.colorrange || {
              gradient: '1',
              minvalue: '0',
              code: '#FCFBFF',
              color: [
                { code: '#FBE1EA', minvalue: '0', maxvalue: '10' },
                { code: '#FEB0BA', minvalue: '10', maxvalue: '20' },
                { code: '#f7f8fd', minvalue: '20', maxvalue: '30' },
                { code: '#DCE8F4', minvalue: '30', maxvalue: '40' },
                { code: '#6B96CB', minvalue: '40', maxvalue: '50' }
              ]
            }
          }
        };

        window.fusionChartInstances[heatmapProductCrcContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapProductCrcContainerId].render();
      } catch (error) {
        heatmapProductCrcContainer.innerHTML = `
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
