/* sw-register.js — register the AlgoPDF service worker for offline use.
 * Service workers require a secure context (https or localhost). */
(function () {
  if (!("serviceWorker" in navigator)) return;
  window.addEventListener("load", function () {
    navigator.serviceWorker.register("sw.js").catch(function (err) {
      console.warn("AlgoPDF: service worker registration failed.", err);
    });
  });
})();
