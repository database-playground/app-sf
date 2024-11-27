import { Controller } from "@hotwired/stimulus";
import { Component, getComponent } from "@symfony/ux-live-component";
import type * as bootstrap from "bootstrap";

export default class extends Controller<HTMLElement> {
  #component: Component | undefined;
  #modal: bootstrap.Modal | undefined;

  async initialize() {
    this.#component = await getComponent(this.element);
  }

  async connect() {
    const bs = await import("bootstrap");
    this.#modal = new bs.Modal(this.element);
  }

  async open() {
    if (!this.#modal || !this.#component) {
      throw new Error("Not applicable.");
    }

    await this.#component.action("instruct");
    this.#modal.hide();
  }
}
