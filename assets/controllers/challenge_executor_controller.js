// @ts-check

import { sql } from "@codemirror/lang-sql";
import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";
import { basicSetup, EditorView } from "codemirror";

/**
 * @extends {Controller<HTMLElement>}
 * @property {string} modelNameValue
 * @property {string} editorSelectorValue
 */
export default class extends Controller {
  static values = {
    modelName: String,
    editorSelector: String,
  };

  /**
   * @type {EditorView | undefined}
   */
  #view;

  /**
   * @type {string | undefined}
   */
  modelNameValue;

  /**
   * @type {string | undefined}
   */
  editorSelectorValue;

  async connect() {
    const component = await getComponent(this.element);

    const modelName = this.modelNameValue;
    const editorSelector = this.editorSelectorValue;

    if (!modelName || !editorSelector) {
      throw new Error("modelName and editorSelector are required.");
    }

    const $editor = this.element.querySelector(editorSelector);
    if (!$editor) {
      throw new Error(`Element not found: ${editorSelector}`);
    }

    this.#view = new EditorView({
      extensions: [
        basicSetup,
        sql(),
        EditorView.updateListener.of((update) => {
          component.set(modelName, update.state.doc.toString(), true, true);
        }),
      ],
      parent: $editor,
    });
  }

  disconnect() {
    super.disconnect();

    this.#view?.destroy();
  }
}
