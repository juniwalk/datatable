
/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

var JuniWalk = JuniWalk || {};

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

		if (this.#activeAction === content.dataset.dtAction) {
			let collapse = bootstrap.Collapse.getOrCreateInstance(content);
			collapse.show();
		}

		this.#isDetailButton = false;
		this.#activeAction = null;
	}
}

naja.registerExtension(new JuniWalk.DataTable.DetailActionExtension);
