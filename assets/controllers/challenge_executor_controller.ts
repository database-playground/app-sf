import { sql } from "@codemirror/lang-sql";
import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";
import { basicSetup, EditorView } from "codemirror";

export default class extends Controller<HTMLElement> {
  static values = {
    modelName: String,
    editorSelector: String,
  };

  declare modelNameValue: string;
  declare editorSelectorValue: string;

  view: EditorView | undefined;

  async connect() {
    const component = await getComponent(this.element);

    const modelName = this.modelNameValue;
    const editorSelector = this.editorSelectorValue;

    const $editor = this.element.querySelector(editorSelector);
    if (!$editor) {
      throw new Error(`Element not found: ${editorSelector}`);
    }

    const lastQuery = this.element.dataset["lastQuery"];

    this.view = new EditorView({
      doc: lastQuery,
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

    this.view?.destroy();
  }
}
