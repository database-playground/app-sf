import {Controller} from '@hotwired/stimulus';
import {EditorView, basicSetup} from "codemirror"
import {sql} from "@codemirror/lang-sql";
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller<HTMLElement> {
    static values = {
        modelName: String,
        editorSelector: String,
    }

    declare modelNameValue: string;
    declare editorSelectorValue: string;

    view: EditorView | undefined

    async connect() {
        const component = await getComponent(this.element);

        const modelName = this.modelNameValue;
        const editorSelector = this.editorSelectorValue;

        const $editor = this.element.querySelector(editorSelector);
        if (!$editor) {
            throw new Error(`Element not found: ${editorSelector}`);
        }

        this.view = new EditorView({
            extensions: [
                basicSetup,
                sql(),
                EditorView.updateListener.of((update) => {
                    component.set(modelName, update.state.doc.toString(), true, true);
                })
            ],
            parent: $editor,
        });
    }

    disconnect() {
        super.disconnect();

        this.view?.destroy();
    }
}
