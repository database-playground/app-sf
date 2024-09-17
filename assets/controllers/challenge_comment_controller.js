// @ts-check

import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";
import * as bootstrap from "bootstrap";

/**
 * @typedef {import("@symfony/ux-live-component").Component} Component
 */

/**
 * @extends {Controller<HTMLElement>}
 */
export default class extends Controller {
  /**
   * @type {Component | undefined}
   */
  #component;

  /**
   * @type {bootstrap.Modal | undefined}
   */
  #modal;

  async initialize() {
    this.#component = await getComponent(this.element);
  }

  async connect() {
    const $commentBlock = this.element.querySelector(".challenge-comments__comment");
    if (!$commentBlock) {
      console.warn("No comment element found. Cannot fade in.");
      return;
    }

    setTimeout(() => {
      // Remove the opacity-0 class to trigger the fade-in animation
      $commentBlock.classList.remove("opacity-0");
    }, 20 /* ms */);

    /**
     * @type {HTMLElement | null}
     */
    const $confirmModal = this.element.querySelector(".challenge-comments__deletion_confirm");
    if ($confirmModal) {
      this.#modal = new bootstrap.Modal($confirmModal);
    }
  }

  /**
   * Confirm if users want to delete the comment.
   *
   * @returns {Promise<void>}
   */
  async confirm() {
    if (!this.#modal) {
      throw new Error("Not applicable.");
    }

    this.#modal?.show();
  }

  /**
   * Delete the comment without confirmation.
   *
   * @returns {Promise<void>}
   */
  async delete() {
    if (!this.#component || !this.#modal) {
      throw new Error("Not applicable.");
    }

    await this.#component.action("delete");

    this.#modal.hide();
  }
}
