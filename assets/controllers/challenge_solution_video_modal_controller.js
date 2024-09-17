// @ts-check

import { Controller } from "@hotwired/stimulus";
import * as bootstrap from "bootstrap";

/**
 * @extends {Controller<HTMLElement>}
 */
export default class extends Controller {
  /**
   * @type {bootstrap.Modal | undefined}
   */
  #modal;

  /**
   * @type {string | undefined}
   */
  #videoUrl;

  connect() {
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
