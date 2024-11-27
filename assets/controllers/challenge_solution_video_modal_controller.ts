import { Controller } from "@hotwired/stimulus";
import type * as bootstrap from "bootstrap";

export default class extends Controller<HTMLElement> {
  #modal: bootstrap.Modal | undefined;
  #videoUrl: string | undefined;

  async connect() {
    const bs = await import("bootstrap");
    this.#modal = new bs.Modal(this.element);
    this.#videoUrl = this.element.dataset.videoUrl;
  }

  open() {
    if (!this.#videoUrl || !this.#modal) {
      throw new Error("Not applicable.");
    }

    window.open(this.#videoUrl, "_blank");
    this.#modal.hide();
  }
}
