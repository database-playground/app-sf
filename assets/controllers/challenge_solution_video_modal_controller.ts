import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLElement> {
  initialize() {
    window.addEventListener("challenge:solution-video:open", (event) => {
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
    });
  }
}
