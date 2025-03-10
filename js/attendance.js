document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();

    // Autofocus on lastName if coming from index.php
    if (document.referrer.includes('index.php')) {
        document.getElementById('lastName').focus();
    }

    // Check if QRCode is loaded
    if (typeof QRCode === 'undefined') {
        return;
    }

    const form = document.getElementById('qrForm');
    const qrResult = document.getElementById('qrResult');
    const qrcodeDiv = document.getElementById('qrcode');
    const downloadBtn = document.getElementById('downloadQR');

    // Add character normalization function
    function normalizeText(text) {
        return text.normalize('NFD')
                  .replace(/[\u0300-\u036f]/g, '')
                  .replace(/[^a-zA-Z0-9\s]/g, '');
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check validity and focus first empty field
        const fields = ['lastName', 'firstName', 'idNumber', 'program'];
        for (let fieldId of fields) {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.focus();
                form.classList.add('was-validated');
                return;
            }
        }
        
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        try {
            // Get form values and normalize them
            const lastName = normalizeText(document.getElementById('lastName').value.trim());
            const firstName = normalizeText(document.getElementById('firstName').value.trim());
            const idNumber = document.getElementById('idNumber').value.trim();
            const program = document.getElementById('program').value.trim();

            // Create minimal data string
            const formData = [
                lastName.substring(0, 10),
                firstName.substring(0, 10),
                idNumber,
                program === 'Regular' ? 'Regular' : 'Special'
            ].join(',');

            // Clear previous QR code
            qrcodeDiv.innerHTML = '';

            // Configure QR code
            new QRCode(qrcodeDiv, {
                text: formData,
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.L,
                version: 4
            });

            // Show QR code section
            qrResult.classList.remove('d-none');

            // Download functionality
            downloadBtn.onclick = function() {
                // Get the QR code element
                const qrImage = qrcodeDiv.querySelector('img, canvas');
                if (!qrImage) {
                    return;
                }

                // Create a canvas to ensure consistent output
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Set dimensions
                canvas.width = 256;
                canvas.height = 256;

                // Fill white background
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Function to handle the actual download
                const downloadImage = () => {
                    // For iOS devices, open in new tab
                    if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                        window.open(canvas.toDataURL('image/png'));
                        return;
                    }

                    // For other devices, trigger download
                    canvas.toDataURL('image/png').replace('image/png', 'image/octet-stream');
                    const link = document.createElement('a');
                    link.download = `qr-code-${idNumber}.png`;
                    link.href = canvas.toDataURL('image/png');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                };

                // Draw QR code onto canvas and trigger download
                if (qrImage.tagName.toLowerCase() === 'canvas') {
                    ctx.drawImage(qrImage, 0, 0, canvas.width, canvas.height);
                    downloadImage();
                } else {
                    // Ensure image is loaded before drawing
                    if (qrImage.complete) {
                        ctx.drawImage(qrImage, 0, 0, canvas.width, canvas.height);
                        downloadImage();
                    } else {
                        qrImage.onload = function() {
                            ctx.drawImage(qrImage, 0, 0, canvas.width, canvas.height);
                            downloadImage();
                        };
                    }
                }
            };
        } catch (error) {
            qrcodeDiv.innerHTML = '<p class="text-danger">Error generating QR code. Please try again.</p>';
        }
    });

    // Validate ID number input
    const idInput = document.getElementById('idNumber');
    idInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });
}); 