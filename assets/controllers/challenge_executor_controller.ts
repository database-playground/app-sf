import { sql } from "@codemirror/lang-sql";
import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";
import { basicSetup, EditorView } from "codemirror";

export default class extends Controller<HTMLElement> {
  static values = {
    editorSelector: String,
    submitButtonSelector: String,
  };

  declare editorSelectorValue: string;
  declare submitButtonSelectorValue: string;

  #editorView: EditorView | undefined;

  async connect() {
    const component = await getComponent(this.element);
    const lastQuery = this.element.dataset["lastQuery"];

    const $editor = this.element.querySelector(this.editorSelectorValue);
    if (!$editor) {
      throw new Error(`Element not found: ${this.editorSelectorValue}`);
    }

    const $submitButton = this.element.querySelector(this.submitButtonSelectorValue);
    if (!$submitButton || !($submitButton instanceof HTMLButtonElement)) {
      throw new Error(`Element not found or not a button: ${this.submitButtonSelectorValue}`);
    }

    // Create the code editor with the last query.
    const editorView = new EditorView({
      doc: lastQuery,
      extensions: [
        basicSetup,
        sql(),
        EditorView.updateListener.of(() => {
          const doc = editorView.state.doc.toString();

          if (doc.trim() === "" || doc === lastQuery) {
            // Disable the button if the user does not query something new.
            $submitButton.disabled = true;
          } else {
            // Enable the button if the user types something.
            $submitButton.disabled = false;
          }
        }),
      ],
      parent: $editor,
    });
    this.#editorView = editorView;

    // If the user presses the submit button, we'll send the query to the server.
    $submitButton.addEventListener("click", async () => {
      const query = editorView.state.doc.toString();

      console.debug("Executing query", { query });
      await component.action("execute", {
        query,
      });
    });
  }

  disconnect() {
    super.disconnect();

    this.#editorView?.destroy();
  }
}
