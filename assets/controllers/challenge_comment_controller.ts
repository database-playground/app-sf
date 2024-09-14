import { Controller } from "@hotwired/stimulus";
import { Component, getComponent } from "@symfony/ux-live-component";
import * as bootstrap from "bootstrap";

export default class extends Controller<HTMLElement> {
  #component: Component | undefined;
  #modal: bootstrap.Modal | undefined;

  async connect() {
    this.#component = await getComponent(this.element);

    const $confirmModal = this.element.querySelector<HTMLElement>(".challenge-comments__deletion_confirm");
    if ($confirmModal) {
      this.#modal = new bootstrap.Modal($confirmModal);
    }
  }

  async confirm() {
    if (!this.#modal) {
      throw new Error("Not applicable.");
    }

    this.#modal?.show();
  }

  async delete() {
    if (!this.#component || !this.#modal) {
      throw new Error("Not applicable.");
    }

    await this.#component.action("delete");

    this.#modal.hide();
  }
}
