import { Controller } from "@hotwired/stimulus";
import * as bootstrap from "bootstrap";

export default class extends Controller<HTMLElement> {
  #modal: bootstrap.Modal | undefined;
  #videoUrl: string | undefined;

  connect(): void {
    this.#modal = new bootstrap.Modal(this.element);
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
