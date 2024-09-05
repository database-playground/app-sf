//@ts-check

import { Controller } from '@hotwired/stimulus';
import {EditorView, basicSetup} from "codemirror"
import {sql} from "@codemirror/lang-sql";
import { getComponent, Component } from '@symfony/ux-live-component';


/**
 * @prop {string} modelNameValue
 * @prop {string} editorSelectorValue
 */
export default class extends Controller {
    static values = {
        modelName: String,
        editorSelector: String,
    }

    async connect() {
        /** @type {Component} */
        const component = await getComponent(this.element);

        const modelName = this.modelNameValue;
        const editorSelector = this.editorSelectorValue;

        this.view = new EditorView({
            extensions: [
                basicSetup,
                sql(),
                EditorView.updateListener.of((update) => {
                    component.set(modelName, update.state.doc.toString(), true, true);
                })
            ],
            parent: this.element.querySelector(editorSelector),
        });
    }

    disconnect() {
        super.disconnect();

        this.view?.destroy();
    }
}
