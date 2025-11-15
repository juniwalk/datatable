
/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

var JuniWalk = JuniWalk || {};

JuniWalk.DataTable = JuniWalk.DataTable || {};
JuniWalk.DataTable.ConfirmExtension = class {
	initialize(naja) {
		naja.uiHandler.addEventListener('interaction', (event) => this.#confirm(event, event.detail.element));
		document.querySelectorAll('[data-dt-confirm]:not(.ajax)').forEach((element) => {
			element.addEventListener('click', (event) => this.#confirm(event, element));
		});
	}

	#confirm(event, element) {
		let question = element.dataset.dtConfirm ?? null;

		if (question && !window.confirm(question)) {
			event.preventDefault();
		}
	}
}

JuniWalk.DataTable = JuniWalk.DataTable || {};
JuniWalk.DataTable.DetailActionExtension = class {
	#isDetailButton = false;
	#activeAction = null;

	initialize(naja) {
		naja.uiHandler.addEventListener('interaction', this.#interaction.bind(this));
		naja.snippetHandler.addEventListener('afterUpdate', this.#afterUpdate.bind(this));
	}

	#interaction(event) {
		let button = event.detail.element;
		let action = button.dataset.dtAction ?? null;
		let target = button.dataset.dtTarget ?? null;

		this.#isDetailButton = action !== null && target !== null;
		this.#activeAction = action;

		if (!this.#isDetailButton) {
			return;
		}

		let snippet = document.querySelector(target);

		if (snippet.classList.contains('d-none')) {
			return;
		}

		let content = snippet.querySelector('.collapse');
		let collapse = bootstrap.Collapse.getOrCreateInstance(content);
		collapse.hide();

		if (this.#activeAction !== content.dataset.dtAction) {
			return;
		}

		collapse.show();
		event.preventDefault();
	}

	#afterUpdate(event) {
		if (!this.#isDetailButton) {
			return;
		}

		let snippet = event.detail.snippet;
		snippet.classList.remove('d-none');

		let content = snippet.querySelector('.collapse');

		if (this.#activeAction === content?.dataset.dtAction) {
			let collapse = bootstrap.Collapse.getOrCreateInstance(content);
			collapse.show();

			this.#isDetailButton = false;
			this.#activeAction = null;
		}
	}
}

JuniWalk.DataTable = JuniWalk.DataTable || {};
JuniWalk.DataTable.AutoSubmitExtension = class {
	#selectorTable = '[data-dt-allow-autosubmit]';
	#selectorInput = '[data-dt-autosubmit]';

	#allowedEvents = ['change', 'keyup'];
	#ignoredKeys = [
		'F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8', 'F9', 'F10', 'F11', 'F12',
		'Esc', 'Shift', 'Control', 'Alt', 'AltGraph', 'CapsLock', 'NumLock',
		'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight',
	];

	initialize(naja) {
		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		document.querySelectorAll(this.#selectorTable).forEach((element) => this.#attach(element));
	}

	#attach(snippet) {
		snippet.querySelectorAll(this.#selectorInput)
			.forEach((element) => {
				let eventType = element.getAttribute(this.#selectorInput.replace(/[\[\]]/g, ''));

				if (!this.#allowedEvents.includes(eventType)) {
					return;
				}

				element.addEventListener(eventType, this.#debounce((event) => this.#submit(element, event)));
			});
	}

	#submit(element, event) {
		if (this.#ignoredKeys.includes(event?.key)) {
			return;
		}

		let request = naja.uiHandler.submitForm(
			element.closest('form')
		);

		return request;
	}

	#debounce(fn, delay = 200) {
		let timer;

		return (...args) => {
			timer && clearTimeout(timer);
			timer = setTimeout(() => fn.apply(this, args), delay);
		};
	}
}

JuniWalk.DataTable = JuniWalk.DataTable || {};
JuniWalk.DataTable.StickyHeader = class {
	#selectorTable = '[data-dt-sticky-header]';
	#selectorThead = 'thead.sticky-top';

	initialize(naja) {
		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		document.querySelectorAll(this.#selectorTable).forEach((element) => this.#attach(element));
	}

	#attach(snippet) {
		snippet.querySelectorAll(this.#selectorThead).forEach((element) => {
			let container = element.closest('table');
			this.#stick(element, container);

			window.addEventListener('scroll', () => this.#stick(element, container));
		});
	}

	#stick(element, container) {
		let coord = container.getBoundingClientRect();
		element.style.transform = coord.y < 0
			? 'translate3d(0, ' + (-coord.y) + 'px, 0)'
			: '';
	}
}

/**
 * @see https://github.com/SortableJS/Sortable
 */
JuniWalk.DataTable = JuniWalk.DataTable || {};
JuniWalk.DataTable.OrderingExtension = class {
	#selectorTable = '[data-dt-allow-ordering] tbody';
	#order = [];

	initialize(naja) {
		if (typeof Sortable !== 'function') {
			console.log('Missing SortableJS component');
			return;
		}

		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		this.#attach(document);
	}

	#attach(snippet) {
		snippet.querySelectorAll(this.#selectorTable)
			.forEach((element) => {
				let sortable = Sortable.create(element, {
					handle: '[data-dt-sort]',
					draggable: 'tr[data-id]',
					ghostClass: 'table-info',
					direction: 'vertical',
					animation: 150,

					onStart: (event) => this.#beforeOrder(event, sortable),
					onSort: (event) => this.#afterOrder(event, sortable),
				});
			});
	}

	#beforeOrder(event, sortable) {
		let table = event.from.closest('[data-dt-table]');
		table.querySelectorAll('.collapse.show').forEach((element) => {
			bootstrap.Collapse.getOrCreateInstance(element).hide();
		});

		this.#order = sortable.toArray();
	}

	#afterOrder(event, sortable) {
		let order = sortable.toArray();
		let delta = {};

		this.#order.forEach((id, index) => {
			// ? Workaround for issue #2456 in SortableJS/Sortable
			// ? @see https://github.com/SortableJS/Sortable/issues/2456
			if (order[index] === undefined) {
				return;
			}

			let value = order.indexOf(id) - index;

			if (value === 0) {
				return;
			}

			delta[id] = value;
		});

		if (!Object.keys(delta).length) {
			return;
		}

		this.#submit(event, delta);
	}

	#submit(event, delta) {
		let name = event.from.closest('[data-dt-table]').dataset.dtTable;
		let url = event.from.dataset.dtSignalOrdering;

		naja.makeRequest('GET', url, {[name+'-delta']: delta});
	}
}

naja.registerExtension(new JuniWalk.DataTable.StickyHeader);
naja.registerExtension(new JuniWalk.DataTable.ConfirmExtension);
naja.registerExtension(new JuniWalk.DataTable.OrderingExtension);
naja.registerExtension(new JuniWalk.DataTable.AutoSubmitExtension);
naja.registerExtension(new JuniWalk.DataTable.DetailActionExtension);
