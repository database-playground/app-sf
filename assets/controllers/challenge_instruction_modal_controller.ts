import { Controller } from "@hotwired/stimulus";
import { Component, getComponent } from "@symfony/ux-live-component";
import * as bootstrap from "bootstrap";

export default class extends Controller<HTMLElement> {
  #component: Component | undefined;
  #modal: bootstrap.Modal | undefined;

  async initialize(): Promise<void> {
    this.#component = await getComponent(this.element);
  }

  connect(): void {
    this.#modal = new bootstrap.Modal(this.element);
  }

  async open() {
    if (!this.#modal || !this.#component) {
      throw new Error("Not applicable.");
    }

    await this.#component.action("instruct");
    this.#modal.hide();
  }
}
