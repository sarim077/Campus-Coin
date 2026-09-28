import html2canvas from 'html2canvas';

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('export-report-image');
    const report = document.getElementById('report-export-area');
    const status = document.getElementById('report-export-status');

    if (!button || !report) return;

    button.addEventListener('click', async () => {
        button.disabled = true;
        if (status) status.textContent = 'Preparing report image.';

        try {
            const canvas = await html2canvas(report, {
                backgroundColor: '#ffffff',
                scale: Math.min(window.devicePixelRatio || 1, 2),
                useCORS: true,
            });
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
            if (!blob) throw new Error('The report image could not be created.');

            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'campus-coin-report.png';
            link.click();
            URL.revokeObjectURL(url);
            if (status) status.textContent = 'Report image downloaded.';
        } catch (error) {
            if (status) status.textContent = error instanceof Error ? error.message : 'The report image could not be created.';
            console.error('Report image export failed.', error);
        } finally {
            button.disabled = false;
        }
    });
});
