<?php
$type = (isset($_GET['type']) && $_GET['type'] === 'image') ? 'image' : 'pdf';

// generating.php only makes sense as a build interstitial. A direct, job-less
// visit has nothing to generate, so send the visitor to the right converter.
if (empty($_GET['job'])) {
    header('Location: ' . ($type === 'image' ? 'pdf-to-image.php' : 'image-to-pdf.php'), true, 302);
    exit;
}

$pageTitle = 'Generating';
$current   = '';
include 'partials/security.php';
include 'partials/head.php';

// What is being generated (safe, short, no HTML).
$label = isset($_GET['label']) ? trim($_GET['label']) : '';
if ($label === '') {
  $label = 'Assembling your document — everything runs on your device.';
}
$label = mb_substr($label, 0, 120);

// Optional next step. Restrict to a local .php path so we never redirect off-site.
$next = isset($_GET['next']) ? trim($_GET['next']) : '';
$nextSafe = '';
if ($next !== '' && preg_match('/^[A-Za-z0-9_\-.\/]+\.php(\?[^#]*)?$/', $next)) {
  $nextSafe = $next;
}

// Job id handed off from another page (e.g. image-to-pdf). Alphanumeric + .-_ only.
$job = isset($_GET['job']) ? trim($_GET['job']) : '';
$jobSafe = preg_match('/^[A-Za-z0-9._\-]+$/', $job) ? $job : '';

// Animation shown while generating. Defaults to the converter's asset; can be
// overridden via ?anim=, but only to a .json inside assets/vendor/.
$animFile = isset($_GET['anim']) ? basename(trim($_GET['anim'])) : '';
if (!preg_match('/^[A-Za-z0-9._\-]+\.json$/', $animFile)) {
  $animFile = $type === 'image' ? 'image-loading.json' : 'pdf-document.json';
}
$animPath = 'assets/vendor/' . $animFile;

// The white image-loading animation needs a dark mat to stay visible in both
// themes; the PDF document animation keeps its own colors, so no mat.
$stageClass = ($type === 'image')
  ? 'w-44 h-56 rounded-lg bg-ink-900 dark:bg-ink-950'
  : 'w-44 h-56';
?>
  <section class="max-w-xl mx-auto px-4 sm:px-6 pt-16 pb-12">
    <div class="card p-8 sm:p-10 flex flex-col items-center text-center">

      <div id="lottie-stage" class="<?php echo $stageClass; ?>" role="img" aria-label="PDF being generated"></div>

      <p id="gen-eyebrow" class="eyebrow mb-2 mt-3">In progress</p>

      <h1 id="gen-title" class="font-display text-[26px] font-semibold text-ink-900 dark:text-paper-50">
        Generating&hellip;
      </h1>

      <p id="gen-label" class="mt-2 text-sm text-ink-500 dark:text-ink-300 max-w-sm leading-relaxed">
        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
      </p>

      <p id="gen-progress" class="mt-3 text-xs text-ink-500 dark:text-ink-300 tabular-nums" hidden></p>

      <div id="gen-status" class="mt-4 w-full max-w-sm" aria-live="polite"></div>

      <div class="mt-8 flex items-center justify-center gap-3">
        <a id="gen-cancel" href="<?php echo $nextSafe !== '' ? htmlspecialchars($nextSafe, ENT_QUOTES, 'UTF-8') : 'index.php'; ?>" class="btn btn-secondary">Cancel</a>
        <button id="gen-download" type="button" class="btn btn-primary hidden">Download PDF</button>
        <?php if ($nextSafe !== ''): ?>
          <a id="gen-continue" href="<?php echo htmlspecialchars($nextSafe, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary hidden">Continue</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Lottie renderer (jsDelivr, same CDN as the other vendor libs). -->
  <script src="https://cdn.jsdelivr.net/npm/lottie-web@5.12.2/build/player/lottie.min.js"></script>
  <script>
    (function () {
      // ---- Animation: cosmetic only, must never block the build ----
      try {
        if (typeof lottie !== "undefined" && document.getElementById("lottie-stage")) {
          var reduceMotion = window.matchMedia &&
            window.matchMedia("(prefers-reduced-motion: reduce)").matches;
          var anim = lottie.loadAnimation({
            container: document.getElementById("lottie-stage"),
            renderer: "svg",
            loop: !reduceMotion,
            autoplay: !reduceMotion,
            path: <?php echo json_encode($animPath); ?>,
            rendererSettings: { preserveAspectRatio: "xMidYMid meet" }
          });
          if (reduceMotion) {
            anim.addEventListener("DOMLoaded", function () {
              anim.goToAndStop(anim.totalFrames / 2, true);
            });
          }
        }
      } catch (e) {
        console.warn("Lottie animation failed to start:", e);
      }

      // ---- Build logic ----
      var eyebrowEl = document.getElementById("gen-eyebrow");
      var titleEl = document.getElementById("gen-title");
      var progressEl = document.getElementById("gen-progress");
      var statusEl = document.getElementById("gen-status");
      var cancelEl = document.getElementById("gen-cancel");
      var continueEl = document.getElementById("gen-continue");
      var downloadEl = document.getElementById("gen-download");
      var jobId = <?php echo $jobSafe !== '' ? json_encode($jobSafe) : 'null'; ?>;
      var type = <?php echo json_encode($type); ?>;

      var pendingBlob = null;
      var pendingName = "";

      if (downloadEl) {
        downloadEl.textContent = (type === "image") ? "Download ZIP" : "Download PDF";
        downloadEl.addEventListener("click", function () {
          if (pendingBlob) Algo.downloadBlob(pendingBlob, pendingName);
        });
      }

      function setDone(name) {
        eyebrowEl.textContent = "Complete";
        titleEl.textContent = (type === "image") ? "Your images are ready" : "Your PDF is ready";
        progressEl.hidden = true;
        if (downloadEl) downloadEl.classList.remove("hidden");
        if (continueEl) continueEl.classList.remove("hidden");
        Algo.setStatus(statusEl, {
          type: "good",
          title: (type === "image") ? "Images ready" : "Your PDF is ready",
          detail: "Save it now, or continue to the next step."
        });
      }

      function setError(msg) {
        eyebrowEl.textContent = "Error";
        titleEl.textContent = "Something went wrong";
        progressEl.hidden = true;
        if (continueEl) continueEl.classList.remove("hidden");
        cancelEl.textContent = "Back to tools";
        cancelEl.setAttribute("href", <?php echo $nextSafe !== '' ? json_encode($nextSafe) : '"index.php"'; ?>);
        Algo.setStatus(statusEl, { type: "bad", title: "Build failed", detail: msg });
      }

      function engineReady() {
        if (!(window.Algo && Algo.takeJob && window.jspdf)) return false;
        if (type === "image") return !!(Algo.buildPdfToImages && window.JSZip);
        return !!Algo.buildImagePdf;
      }

      function runJob() {
        if (!jobId) return; // Manual / standalone use — just show the animation.
        if (!engineReady()) {
          setError("The build engine failed to load. Please try again.");
          return;
        }
        Algo.takeJob(jobId).then(function (job) {
          if (!job) throw new Error("The build job could not be found. Please start again.");

          if (type === "image") {
            return Algo.buildPdfToImages(job.file, {
              format: (job.opts && job.opts.format) || "image/png",
              scale: (job.opts && job.opts.scale) || 2
            }).then(function (results) {
              if (!results || !results.length) throw new Error("No pages were produced.");
              var zip = new JSZip();
              results.forEach(function (r) { zip.file(r.name, r.blob); });
              return zip.generateAsync({ type: "blob" }).then(function (blob) {
                var name = (job.name && job.name.indexOf(".zip") !== -1) ? job.name : "algopdf-images.zip";
                pendingBlob = blob;
                pendingName = name;
                Algo.downloadBlob(blob, name); // auto-download once
                setDone(name);
              });
            });
          }

          return Algo.buildImagePdf(job.files, {
            size: (job.opts && job.opts.size) || "original",
            fit: (job.opts && job.opts.fit) || "contain",
            onProgress: function (pct, label) {
              progressEl.hidden = false;
              progressEl.textContent = Math.round(pct) + "% · " + label;
            }
          }).then(function (blob) {
            var name = job.name || "algopdf-document.pdf";
            pendingBlob = blob;
            pendingName = name;
            Algo.downloadBlob(blob, name); // auto-download once
            setDone(name);
          });
        }).catch(function (err) {
          setError((err && err.message) || "Unknown error.");
        });
      }

      // The engine (jsPDF) and Algo helpers load via the footer scripts. Rather
      // than assume a load event, poll briefly until they're ready, then run.
      var tries = 0;
      function startWhenReady() {
        if (engineReady()) { runJob(); return; }
        if (++tries > 100) { // ~6s without the engine → surface a real error.
          setError("The build engine failed to load. Please try again.");
          return;
        }
        setTimeout(startWhenReady, 60);
      }
      if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", startWhenReady);
      } else {
        startWhenReady();
      }
    })();
  </script>

<?php if ($type === 'image'): ?>
  <!-- PDF→image builder (loads pdf.js as a module) -->
  <script type="module" src="assets/js/build-image.js"></script>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
