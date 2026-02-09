Livewire.on('scroll-to-top', () => {
  jQuery(document).ready(function () {
    console.log('Se hace scroll');
    window.scrollTo({
      top: 0,
      behavior: 'smooth' // Hace el desplazamiento suave
    });
  });
});

Livewire.on('show-notification', data => {
  jQuery(document).ready(function () {
    // Configura el mensaje y el tipo de toast basado en los datos recibidos
    // Extrae el primer objeto del array `data` para obtener los valores `type` y `message`
    const { type, message } = data[0]; // Desestructuración para obtener `type` y `message`

    showToast(message, type);

    function showToast(message, type) {
      let messageType = 'bg-primary';

      // Configura el tipo de mensaje basado en el tipo proporcionado
      if (type === 'success') {
        messageType = 'bg-primary';
      } else if (type === 'error') {
        messageType = 'bg-danger';
      } else if (type === 'warning') {
        messageType = 'bg-warning';
      } else if (type === 'info') {
        messageType = 'bg-info';
      }

      // Selecciona los elementos del toast
      let toastElement = document.querySelector('.toast-ex');
      let toastMessage = document.querySelector('#toast-message');

      // Quita clases previas de tipo y animación
      toastElement.classList.remove('bg-success', 'bg-danger', 'bg-primary', 'bg-warning', 'bg-info');

      // Configura animación y posición
      let selectedAnimation = 'animate__tada';
      let places = 'top-0 end-0'; // Posición del toast
      let selectedPlacement = places.split(' ');

      // Agrega la clase de tipo y posición
      toastElement.classList.add(messageType, selectedAnimation);
      DOMTokenList.prototype.add.apply(toastElement.classList, selectedPlacement);

      // Define el mensaje en el toast
      toastMessage.innerHTML = message;

      // Inicializa y muestra el toast
      let toastInstance = new bootstrap.Toast(toastElement);
      toastInstance.show();
    }
  });
});

Livewire.on('show-confirmation-dialog', event => {
  const {
    recordId,
    recordIds,
    componentName,
    methodName,
    title = '¿Está seguro?',
    message = '¡No podrá revertir esta acción!',
    confirmText = 'Sí, proceder',
    cancelText = 'Cancelar'
  } = event[0];

  Swal.fire({
    title: title,
    html: message,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    customClass: {
      confirmButton: 'btn btn-primary me-3',
      cancelButton: 'btn btn-label-secondary'
    },
    buttonsStyling: false,
    backdrop: true
  }).then(result => {
    if (result.isConfirmed) {
      // Si hay recordIds (batch), pasarlos; si no, pasar recordId (single)
      if (recordIds && Array.isArray(recordIds)) {
        Livewire.dispatchTo(componentName, methodName, { recordIds: recordIds });
      } else {
        Livewire.dispatchTo(componentName, methodName, { recordId: recordId });
      }
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      Swal.fire({
        title: 'Cancelado',
        html: 'La acción fue cancelada.',
        icon: 'info',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
    }
  });
});

// Nuevo evento para notas de crédito con campo de motivo
Livewire.on('show-creditnote-dialog', event => {
  const {
    recordId,
    componentName,
    methodName,
    title = 'Generar Nota de Crédito',
    message = 'Por favor, indique el motivo de la nota de crédito:',
    confirmText = 'Generar Nota',
    cancelText = 'Cancelar',
    inputPlaceholder = 'Motivo de la nota de crédito...'
  } = event[0];

  Swal.fire({
    title: title,
    html: message,
    icon: 'warning',
    input: 'text',
    inputPlaceholder: inputPlaceholder,
    inputAttributes: {
      required: 'true',
      'aria-label': 'Motivo de la nota de crédito',
      autocapitalize: 'off'
    },
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    customClass: {
      confirmButton: 'btn btn-primary me-3',
      cancelButton: 'btn btn-label-secondary'
      //input: 'form-control'  // Clase para estilizar el textarea
    },
    buttonsStyling: false,
    backdrop: true,
    preConfirm: reason => {
      if (!reason || reason.trim() === '') {
        Swal.showValidationMessage('El motivo es obligatorio');
        return false;
      }
      return reason;
    }
  }).then(result => {
    if (result.isConfirmed) {
      const motivo = result.value;
      console.log(
        'El texto introducido es: ' + motivo,
        'Componente: ' + componentName + 'methodo: ' + methodName + ' RecordId: ' + motivo
      );
      Livewire.dispatchTo(componentName, methodName, {
        recordId: recordId,
        motivo: motivo
      });
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      Swal.fire({
        title: 'Operación cancelada',
        text: 'No se generó la nota de crédito',
        icon: 'info',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
    }
  });
});

// ✅ Función para Select2 + AJAX + Livewire
window.select2LivewireAjax = ({
  wireModelName,
  url,
  postUpdate = true,
  dropdownParent = null,
  placeholder = 'Buscar...',
  onSelect = null // Nuevo callback
}) => ({
  init(el) {
    if (!el) return;

    const livewireComponent = window.Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'));
    if (!livewireComponent) return;

    const initializeSelect2 = () => {
      if ($(el).hasClass('select2-hidden-accessible')) {
        $(el).off('change').select2('destroy');
      }

      const config = {
        width: 'style',
        placeholder: placeholder,
        allowClear: true,
        ajax: {
          url: url,
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term
            };
          },
          processResults: function (data) {
            return {
              results: data
            };
          },
          cache: true
        },
        minimumInputLength: 2
      };

      if (dropdownParent) {
        config.dropdownParent = $(dropdownParent);
      }

      $(el).select2(config);

      $(el).on('change', () => {
        const newValue = $(el).val();
        livewireComponent.set(wireModelName, newValue, postUpdate);

        if (onSelect && newValue) {
          Livewire.dispatch(onSelect, { contactId: newValue });
        }
      });
    };

    initializeSelect2();

    Livewire.on('select2:refresh', data => {
      const id = data?.id || data?.[0]?.id;
      if (el.id === id) {
        setTimeout(() => initializeSelect2(), 50);
      }
    });
  }
});

// ✅ Función mejorada para Select2 + Livewire con dependencias y opcionalidades
window.select2Livewire = ({
  wireModelName,
  postUpdate = true,
  isMultiple = false,
  dropdownParent = null,
  readonlyVisual = false
}) => ({
  init(el) {
    if (!el) {
      console.warn('[select2Livewire] El select no está definido.');
      return;
    }

    const livewireComponent = window.Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'));
    if (!livewireComponent) {
      console.error('[select2Livewire] Componente Livewire no encontrado.');
      return;
    }

    const initializeSelect2 = () => {
      if ($(el).hasClass('select2-hidden-accessible')) {
        $(el).off('change').select2('destroy');
      }

      const config = {
        width: 'style'
      };

      if (dropdownParent) {
        config.dropdownParent = $(dropdownParent);
      }

      $(el).select2(config);

      // Establece valor desde Livewire
      const current = livewireComponent.get(wireModelName);
      if (current !== undefined && current !== null) {
        $(el).val(current).trigger('change');
      }

      // Escucha cambios
      $(el).on('change', () => {
        const newValue = $(el).val();
        const value = isMultiple ? newValue ?? [] : newValue;
        livewireComponent.set(wireModelName, value, postUpdate);
      });

      // Permitir abrir el dropdown con flecha ↓ desde el contenedor visible
      const container = $(el).next('.select2-container');
      const searchbox = container.find('.select2-selection');

      searchbox.off('keydown').on('keydown', function (event) {
        if (event.key === 'ArrowDown') {
          console.log('[select2Livewire] Abriendo dropdown con flecha ↓');
          $(el).select2('open');
          event.preventDefault();
        }
      });

      // Visualmente readonly
      if (readonlyVisual) {
        const container = $(el).next('.select2-container');
        container.css({
          'pointer-events': 'none',
          'background-color': '#f8f9fa', // Bootstrap .bg-light
          opacity: '1' // no lo opacamos, solo lo hacemos no interactivo
        });
      }
    };

    // Inicializa al cargar
    initializeSelect2();

    // Permitir reinit desde Livewire
    Livewire.on('select2:refresh', data => {
      const id = data?.id || data?.[0]?.id;
      if (el.id === id) {
        console.log('Se ha inicializado el select2 ' + id);
        setTimeout(() => initializeSelect2(), 50); // asegurarse de que el DOM esté listo
      }
    });

    Livewire.on('clearFilterselect2', () => {
      // Recorre todos los select2 asociados a filtros y reinícialos
      document.querySelectorAll('select.select2').forEach(select => {
        const id = select.id;

        console.log('Se ha inicializado el select2 ' + id);

        if ($(select).hasClass('select2-hidden-accessible')) {
          $(select).val('').trigger('change');

          // Si tienes una función de reinicio controlado
          Livewire.dispatch('select2:refresh', { id: id });
        }
      });
    });
  }
});

window.select2LivewireMultipleWithToggle = ({ wireModelName, postUpdate = true }) => ({
  init(el) {
    if (!el) {
      console.warn('[select2LivewireMultipleWithToggle] No se encontró el select.');
      return;
    }

    const livewireComponent = window.Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'));
    if (!livewireComponent) {
      console.error('No se encontró el componente Livewire para el select:', el);
      return;
    }

    $(el).select2({
      dropdownParent: $(el).parent(),
      closeOnSelect: true
    });

    // Set inicial
    const initial = livewireComponent.get(wireModelName);
    if (Array.isArray(initial)) {
      $(el).val(initial).trigger('change');
    }

    // Evento de cambio
    $(el).on('change', () => {
      const value = $(el).val() ?? [];
      if (postUpdate) {
        livewireComponent.set(wireModelName, value);
      } else {
        livewireComponent.set(wireModelName, value, false);
      }
    });

    // Agregar botón toggle al abrir el dropdown
    $(el).on('select2:open', function () {
      const dropdown = $('.select2-container--open .select2-dropdown');
      const results = dropdown.find('.select2-results');

      const toggleId = `s2-togall-${el.id}`;
      if ($('#' + toggleId).length > 0) return;

      const toggleButton = $(`
        <span id="${toggleId}" class="s2-togall-button" style="cursor:pointer; padding: 5px 10px; display: flex; justify-content: space-between; align-items: center;">
          <span class="s2-select-label"><i class="fa fa-square me-2 text-secondary"></i> Seleccionar todo</span>
          <span class="s2-unselect-label" style="display:none;"><i class="fa fa-check-square text-danger me-2"></i> Deseleccionar todo</span>
        </span>
      `);

      results.before(toggleButton);

      const toggle = () => {
        const allOptions = $(el)
          .find('option')
          .map((_, o) => o.value)
          .get();
        const selected = $(el).val() ?? [];

        const isAllSelected = selected.length === allOptions.length;
        if (isAllSelected) {
          $(el).val([]).trigger('change');
        } else {
          $(el).val(allOptions).trigger('change');
        }

        // Cierra el dropdown luego de la acción
        $(el).select2('close');
      };

      toggleButton.on('click', toggle);
    });

    // Sincronizar estado visual del botón con selección
    $(el).on('change', function () {
      const toggle = $(`#s2-togall-${el.id}`);
      const all = $(el)
        .find('option')
        .map((_, o) => o.value)
        .get();
      const selected = $(el).val() ?? [];
      const allSelected = selected.length === all.length;

      toggle.find('.s2-select-label').toggle(!allSelected);
      toggle.find('.s2-unselect-label').toggle(allSelected);
    });

    // 🚨 Nuevo: evento para limpiar select2 visualmente
    Livewire.on('clearFilterselect2', () => {
      $(el).val([]).trigger('change');
      console.log('Se inicializó el select2');
    });
  }
});

window.rangePickerLivewire = ({ wireEventName = 'dateRangeSelected' }) => ({
  init(el) {
    if (!el || el.flatpickrInstance) return;

    const dispatchRange = range => {
      const rangePickerId = el.getAttribute('id') || null;
      Livewire.dispatch(wireEventName, {
        id: rangePickerId,
        range: range
      });
    };

    el.flatpickrInstance = flatpickr(el, {
      mode: 'range',
      allowInput: true,
      dateFormat: 'd-m-Y',

      onClose: function (selectedDates, dateStr) {
        if (selectedDates.length === 2) {
          console.log('Livewire dispatch:', wireEventName, { id: el.id, range: dateStr });
          dispatchRange(dateStr);
        } else if (selectedDates.length === 0 || el.value === '') {
          dispatchRange('');
        }
      },

      // Nueva línea: se dispara cuando el valor cambia (teclado, pegado, etc.)
      onChange: function (selectedDates, dateStr) {
        console.log('onChange:', selectedDates, dateStr);
        if (selectedDates.length === 2) {
          dispatchRange(dateStr);
        } else if (selectedDates.length === 0 || el.value === '') {
          dispatchRange('');
        }
      },

      onValueUpdate: function () {
        // Detectar limpieza desde el botón de borrar
        if (el.value === '') {
          dispatchRange('');
        }
      }
    });

    // Detectar limpieza manual (ej. teclando Backspace/Delete)
    el.addEventListener('input', () => {
      if (el.value === '') {
        dispatchRange('');
      }
    });
  }
});

function cleaveLivewire({
  initialValue = '',
  postUpdate = false,
  wireModelName = null,
  decimalScale = 2,
  allowNegative = false,
  prefix = '',
  delimiter = ',',
  decimalMark = '.',
  rawValueCallback = null,
  watchProperty = null, // ← esto es nuevo
  disableWhen = null // ← esto es nuevo
} = {}) {
  return {
    rawValue: initialValue,
    cleaveInstance: null,
    init(el) {
      if (!el) {
        console.warn('cleaveLivewire: El elemento no está disponible aún.');
        return;
      }

      // 🧠 Fallback: si no se pasa watchProperty, usar wireModelName
      // quito esto porque no funciona con los repeater
      //watchProperty = watchProperty || wireModelName;

      el.value = this.rawValue;

      this.cleaveInstance = new Cleave(el, {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: decimalMark,
        delimiter: delimiter,
        numeralDecimalScale: decimalScale,
        numeralPositiveOnly: !allowNegative,
        prefix: prefix,
        noImmediatePrefix: true,
        rawValueTrimPrefix: true
      });

      el.addEventListener('input', () => {
        this.rawValue = this.cleaveInstance.getRawValue();

        if (typeof rawValueCallback === 'function') {
          rawValueCallback(this.rawValue);
        }

        if (postUpdate && wireModelName && typeof window.Livewire !== 'undefined') {
          Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id')).set(wireModelName, this.rawValue);
        }
      });

      if (watchProperty && typeof this.$watch === 'function') {
        console.log('watch');
        this.$watch(watchProperty, value => {
          if (this.cleaveInstance) {
            this.cleaveInstance.setRawValue(value);
            this.rawValue = value;
          } else {
            el.value = value;
          }
        });
      }

      // Observar cambios si se proporciona watchProperty
      if (watchProperty && typeof disableWhen === 'function' && typeof this.$watch === 'function') {
        this.$watch(watchProperty, value => {
          el.disabled = disableWhen(value);
        });

        // Evaluación inicial
        el.disabled = disableWhen(this[watchProperty]);
      }
    }
  };
}

// Opcional: exponer explícitamente al global
window.cleaveLivewire = cleaveLivewire;

window.initAllCleave = function () {
  const elements = document.querySelectorAll('.cleave-init');

  elements.forEach(el => {
    if (el._cleaveInitialized) return;

    const decimals = parseInt(el.dataset.decimals ?? '2');
    const allowNegative = el.dataset.allowNegative === 'true';
    const wireModel = el.dataset.model ?? null;
    const initialValue = el.dataset.initial ?? '';
    const prefix = el.dataset.prefix ?? '';

    const cleave = new Cleave(el, {
      numeral: true,
      numeralThousandsGroupStyle: 'thousand',
      numeralDecimalMark: '.',
      delimiter: ',',
      numeralDecimalScale: decimals,
      numeralPositiveOnly: !allowNegative,
      prefix: prefix
    });

    if (initialValue !== '') {
      cleave.setRawValue(initialValue);
    }

    // Almacenar la instancia
    el._cleaveInstance = cleave;
    el._cleaveInitialized = true;

    el.addEventListener('input', () => {
      const rawValue = cleave.getRawValue();

      if (wireModel && typeof window.Livewire !== 'undefined') {
        const component = Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'));
        if (component) {
          component.set(wireModel, rawValue);
        }
      }
    });
  });
};

// Ejecutar al cargar
document.addEventListener('DOMContentLoaded', () => {
  window.initAllCleave();
});

// Escuchar desde Livewire para re-inicializar cuando sea necesario
Livewire.on('refreshCleave', () => {
  console.log('refreshCleave custom');
  setTimeout(() => window.initAllCleave(), 300); // pequeña espera para que el DOM esté listo
});

Livewire.on('date-picker:refresh', data => {
  const { id, date } = Array.isArray(data) ? data[0] : data;
  const el = document.getElementById(id);
  if (el && el.flatpickrInstance) {
    //console.log('Refreshing date picker', id, date);
    el.flatpickrInstance.setDate(date);
  }
});

function numeroALetras(num) {
  const UNIDADES = ['CERO', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
  const DECENAS = [
    'DIEZ',
    'ONCE',
    'DOCE',
    'TRECE',
    'CATORCE',
    'QUINCE',
    'DIECISEIS',
    'DIECISIETE',
    'DIECIOCHO',
    'DIECINUEVE'
  ];
  const DECENAS_MAYORES = [
    'VEINTE',
    'VEINTIUN',
    'VEINTIDOS',
    'VEINTITRES',
    'VEINTICUATRO',
    'VEINTICINCO',
    'VEINTISEIS',
    'VEINTISIETE',
    'VEINTIOCHO',
    'VEINTINUEVE'
  ];
  const CENTENAS = [
    'CIEN',
    'DOSCIENTOS',
    'TRESCIENTOS',
    'CUATROCIENTOS',
    'QUINIENTOS',
    'SEISCIENTOS',
    'SETECIENTOS',
    'OCHOCIENTOS',
    'NOVECIENTOS'
  ];
  const MIL = 'MIL';
  const MILLON = 'MILLON';
  const MILLONES = 'MILLONES';

  function convertirUnidades(num) {
    return UNIDADES[num];
  }

  function convertirDecenas(num) {
    if (num < 10) return convertirUnidades(num);
    if (num < 20) return DECENAS[num - 10];
    if (num < 30) return DECENAS_MAYORES[num - 20];
    const decena = Math.floor(num / 10);
    const unidad = num % 10;
    const decenasText = ['TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    return unidad === 0 ? decenasText[decena - 3] : decenasText[decena - 3] + ' Y ' + convertirUnidades(unidad);
  }

  function convertirCentenas(num) {
    if (num === 100) return 'CIEN';
    if (num < 100) return convertirDecenas(num);
    const centena = Math.floor(num / 100);
    const resto = num % 100;

    // Aquí cambiamos el manejo de "CIENTO"
    if (centena === 1 && resto > 0) {
      return 'CIENTO ' + convertirDecenas(resto);
    }
    return CENTENAS[centena - 1] + (resto > 0 ? ' ' + convertirDecenas(resto) : '');
  }

  function convertirMiles(num) {
    if (num === 1000) return MIL;
    if (num < 1000) return convertirCentenas(num);
    const miles = Math.floor(num / 1000);
    const resto = num % 1000;
    const milesText = miles === 1 ? MIL : convertirCentenas(miles) + ' ' + MIL;
    return resto === 0 ? milesText : milesText + ' ' + convertirCentenas(resto);
  }

  /*
    function convertirMillones(num) {
        if (num === 1000000) return MILLON;
        if (num < 1000000) return convertirMiles(num);
        const millones = Math.floor(num / 1000000);
        const resto = num % 1000000;
        const millonesText = millones === 1 ? MILLON : convertirCentenas(millones) + ' ' + MILLONES;
        return resto === 0 ? millonesText : millonesText + ' ' + convertirMiles(resto);
    }
    */
  function convertirMillones(num) {
    if (num === 1000000) return 'UN MILLON'; // Cambiar "MILLON" a "UN MILLON"
    if (num < 1000000) return convertirMiles(num);
    const millones = Math.floor(num / 1000000);
    const resto = num % 1000000;

    // Si el número de millones es 1, devolver "UN MILLON"
    const millonesText = millones === 1 ? 'UN MILLON' : convertirCentenas(millones) + ' ' + MILLONES;
    return resto === 0 ? millonesText : millonesText + ' ' + convertirMiles(resto);
  }

  function convertirParteEntera(num) {
    if (num === 0) return 'CERO';
    if (num < 100) return convertirDecenas(num);
    if (num < 1000) return convertirCentenas(num);
    if (num < 1000000) return convertirMiles(num);
    return convertirMillones(num);
  }

  function convertirParteDecimal(num) {
    return num < 10 ? '0' + num : num;
  }

  function ajustarUnidades(texto) {
    if (!texto) return '';

    // Primero aplicar correcciones de UNO -> UN antes de MIL/MILLON
    let res = texto
      .replace(/\bUNO\s+MIL\b/g, 'UN MIL')
      .replace(/\bUNO\s+MILLON\b/g, 'UN MILLON')
      .replace(/\bUNO\s+MILLONES\b/g, 'UN MILLONES')
      .replace(/\bCIENTO\s+UNO\s+MIL\b/g, 'CIENTO UN MIL')
      .replace(/\bCIENTO\s+UNO\s+MILLON\b/g, 'CIENTO UN MILLON')
      .replace(/\bCIENTO\s+UNO\s+MILLONES\b/g, 'CIENTO UN MILLONES');

    // Ahora manejar el caso de VEINTIUN / VEINTIUNO
    // Se usa VEINTIUNO solo si es el final de la cadena o si le sigue " CON "
    if (res === 'VEINTIUN') return 'VEINTIUNO';

    if (res.endsWith(' VEINTIUN')) {
      res = res.replace(/ VEINTIUN$/, ' VEINTIUNO');
    }

    if (res.includes('VEINTIUN CON ')) {
      res = res.replace(/VEINTIUN CON /g, 'VEINTIUNO CON ');
    }

    return res;
  }

  const parteEntera = Math.floor(num);
  const parteDecimal = Math.round((num - parteEntera) * 100);

  let texto = convertirParteEntera(parteEntera);
  texto += ' CON ' + convertirParteDecimal(parteDecimal) + '/100';

  return ajustarUnidades(texto).toUpperCase();
}

// 👇 Esto expone la función al entorno global
// ✅ Esta línea es CRUCIAL para Vite con @vite(...) en Blade
if (typeof window !== 'undefined') {
  window.numeroALetras = numeroALetras;
}

// Ejemplos de uso
//console.log(numeroALetras(21));          // "VEINTIUNO CON 00/100"
//console.log(numeroALetras(201599.30));   // "DOSCIENTOS UN MIL QUINIENTOS NOVENTA Y NUEVE CON 30/100"

// DatePicker con capacidad de copiar/pegar
window.datePickerLivewire = ({ wireEventName = 'dateSelected' }) => ({
  init(el) {
    if (!el || el.flatpickrInstance) return;

    // Debounce para prevenir múltiples llamadas
    let debounceTimer;
    let fpInstance; // Guardaremos la instancia de Flatpickr aquí

    const dispatchDate = date => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        const datePickerId = el.getAttribute('id') || null;
        Livewire.dispatch(wireEventName, {
          id: datePickerId,
          date: date
        });
      }, 300); // 300ms de debounce
    };

    // Inicializar Flatpickr con locale manual
    el.flatpickrInstance = flatpickr(el, {
      allowInput: true,
      dateFormat: 'd-m-Y',
      //locale: spanishLocale,
      disableMobile: true,
      onReady: function (selectedDates, dateStr, instance) {
        // Guardar la instancia para poder cerrarla después
        fpInstance = instance;

        // Remover atributo readonly para permitir copiar/pegar
        instance.input.removeAttribute('readonly');
      },
      onClose: function (selectedDates, dateStr) {
        dispatchDate(dateStr);
      },
      onChange: function (selectedDates, dateStr) {
        // Solo se dispara si hay una fecha válida
        if (selectedDates.length > 0) {
          dispatchDate(dateStr);
        }
      }
    });

    // Detectar limpieza manual
    el.addEventListener('input', () => {
      if (el.value === '') {
        dispatchDate('');

        // Cerrar el datepicker después de seleccionar
        if (fpInstance) {
          setTimeout(() => {
            fpInstance.close();
          }, 100); // Pequeño retardo para permitir que se complete la selección
        }
      }
    });
  }
});
/*
Livewire.on('updateDatePicker', data => {
  const { id, date } = data;
  const el = document.getElementById(id);
  if (el && el.flatpickrInstance) {
    el.flatpickrInstance.setDate(date, false);
  }
});
*/
