import { Controller } from "@hotwired/stimulus";
import { Component, getComponent } from "@symfony/ux-live-component";
import * as bootstrap from "bootstrap";

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

    modal.hide();

    // FIXME: action() does not resolve. Might be a deadlock in the component?
    await this.#component.action("delete", {}, 200);
  }
}
