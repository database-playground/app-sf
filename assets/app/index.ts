import * as bootstrap from "bootstrap";
import "./stimulus.ts";

/**
 * Initialize tooltips of Bootstrap
 */
document.addEventListener("turbo:load", () => {
  const tooltipTriggerList = document.querySelectorAll("[data-bs-toggle=\"tooltip\"]");
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

  // Destroy tooltips when navigating to a new page
  document.addEventListener("turbo:before-visit", () => {
    for (const tooltip of tooltipList) {
      tooltip.dispose();
    }
  }, {
    once: true,
  });
});
