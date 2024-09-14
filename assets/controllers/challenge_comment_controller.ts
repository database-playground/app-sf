import { Controller } from "@hotwired/stimulus";
import { Component, getComponent } from "@symfony/ux-live-component";
import bootstrap from "bootstrap";

export default class extends Controller<HTMLElement> {
  #component: Component | undefined;

  async connect() {
    this.#component = await getComponent(this.element);
  }

  async delete() {
    if (!this.#component) {
      throw new Error("Component not found");
    }

    const modal = new bootstrap.Modal("#challenge-comment-confirm-modal");

    await this.#component.action("delete", {}, 200);
    modal.hide(); // Hide the modal after the action is triggered
  }
}
