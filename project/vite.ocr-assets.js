import { createReadStream, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = fileURLToPath(new URL('.', import.meta.url));
const ocrAssets = new Map([
    ['worker.min.js', resolve(projectRoot, 'node_modules/tesseract.js/dist/worker.min.js')],
    ['tesseract-core-lstm.wasm.js', resolve(projectRoot, 'node_modules/tesseract.js-core/tesseract-core-lstm.wasm.js')],
    ['tesseract-core-simd-lstm.wasm.js', resolve(projectRoot, 'node_modules/tesseract.js-core/tesseract-core-simd-lstm.wasm.js')],
    ['tesseract-core.wasm.js', resolve(projectRoot, 'node_modules/tesseract.js-core/tesseract-core.wasm.js')],
    ['tesseract-core-simd.wasm.js', resolve(projectRoot, 'node_modules/tesseract.js-core/tesseract-core-simd.wasm.js')],
    ['eng.traineddata.gz', resolve(projectRoot, 'node_modules/@tesseract.js-data/eng/4.0.0_best_int/eng.traineddata.gz')],
]);

export function serveAndBundleOcrAssets() {
    return {
        name: 'serve-and-bundle-local-ocr-assets',
        configureServer(server) {
            server.middlewares.use('/ocr-assets', (request, response, next) => {
                const fileName = new URL(request.url, 'http://localhost').pathname.slice(1);
                const filePath = ocrAssets.get(fileName);

                if (!filePath) {
                    next();
                    return;
                }

                response.setHeader('Content-Type', fileName.endsWith('.gz') ? 'application/gzip' : 'application/javascript');
                createReadStream(filePath).on('error', next).pipe(response);
            });
        },
        generateBundle() {
            for (const [fileName, filePath] of ocrAssets) {
                this.emitFile({
                    type: 'asset',
                    fileName: `assets/ocr/${fileName}`,
                    source: readFileSync(filePath),
                });
            }
        },
    };
}
