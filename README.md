# devjandreiii

Open-source, browser-only document and QR tools. Files never leave your device.

## Apps

- **AlgoPDF** — `algopdf/`
  - PDF to Image
  - Image to PDF
  - Watermark PDF
- **QR Code Generator** — `qrcode/`

## Why browser-only

No uploads, no accounts, no tracking. Conversion runs entirely on your device using open-source libraries loaded from CDNs, or fully offline once cached.

## Tech

- PHP pages with Tailwind CSS
- Client-side libraries: jsPDF, PDF.js, JSZip, qrcode
- Service Worker for offline caching
- Font Awesome 6.5.1 for icons

## Local setup

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`.

## Design

See `design-system.md` for the LORCAPP UI system used across the apps.

## License

MIT
