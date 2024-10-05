import { Controller } from "@hotwired/stimulus";
import { Component, getComponent } from "@symfony/ux-live-component";
import * as bootstrap from "bootstrap";

export default class extends Controller<HTMLElement> {
  #component: Component | undefined;
  #modal: bootstrap.Modal | undefined;

  async initialize() {
    this.#component = await getComponent(this.element);
  }

  async connect() {
    const $commentBlock = this.element.querySelector(".app-challenge-comment__main");
    if (!$commentBlock) {
      console.warn("No comment element found. Cannot fade in.");
      return;
    }

    setTimeout(() => {
      // Remove the opacity-0 class to trigger the fade-in animation
      $commentBlock.classList.remove("opacity-0");
    }, 20 /* ms */);

    const $confirmModal = this.element.querySelector<HTMLElement>(".app-challenge-comment__deletion_confirm");
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
