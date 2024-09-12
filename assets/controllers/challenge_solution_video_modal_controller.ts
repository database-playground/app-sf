import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLElement> {
  initialize() {
    // Safari may open multiple tabs if the event is triggered multiple times
    // This debounce is to prevent that from happening
    let debounce: number | undefined;

    window.addEventListener("challenge:solution-video:open", (event) => {
      clearTimeout(debounce);

      debounce = setTimeout(() => {
        if (
          "detail" in event
          && event.detail
          && typeof event.detail === "object"
          && "solutionVideo" in event.detail
          && event.detail.solutionVideo
          && typeof event.detail.solutionVideo === "string"
        ) {
          window.open(event.detail.solutionVideo, "_blank");
        } else {
          throw new Error("Invalid event detail. Expected { solutionVideo: string }");
        }
      }, 200);
    });
  }
}
