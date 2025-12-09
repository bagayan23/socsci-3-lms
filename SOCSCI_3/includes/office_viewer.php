<?php
// Office Document Viewer
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['file'])) {
    die('No file specified');
}

$filePath = $_GET['file'];
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Viewer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 10px;
            margin: 0;
        }
        .viewer-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            min-height: calc(100vh - 20px);
            display: flex;
            flex-direction: column;
        }
        .viewer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .viewer-header h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .download-btn {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .viewer-content {
            padding: 20px;
            min-height: 400px;
            overflow: auto;
            flex: 1;
        }
        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #667eea;
        }
        .loading i {
            font-size: 3rem;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .error {
            text-align: center;
            padding: 60px 20px;
            color: #ef4444;
        }
        .error i {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        /* Excel table styles */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.9rem;
            min-width: 600px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        th {
            background: #f8fafc;
            font-weight: 600;
            color: #334155;
        }
        tr:hover {
            background: #f8fafc;
        }
        /* Word document styles */
        .document-content {
            line-height: 1.8;
            color: #1e293b;
        }
        .document-content p {
            margin-bottom: 1em;
        }
        .document-content h1, .document-content h2, .document-content h3 {
            margin: 1.5em 0 0.5em 0;
            color: #0f172a;
        }
        .document-content ul, .document-content ol {
            margin-left: 2em;
            margin-bottom: 1em;
        }
        .document-content img {
            max-width: 100%;
            height: auto;
        }
        /* PowerPoint styles */
        .ppt-message {
            text-align: center;
            padding: 60px 20px;
        }
        .ppt-message i {
            font-size: 4rem;
            color: #d24726;
            margin-bottom: 20px;
        }
        .ppt-message h2 {
            color: #334155;
            margin-bottom: 15px;
        }
        .ppt-message p {
            color: #64748b;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        @media (max-width: 768px) {
            body {
                padding: 5px;
            }
            .viewer-container {
                border-radius: 8px;
                min-height: calc(100vh - 10px);
            }
            .viewer-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px 20px;
            }
            .viewer-header h1 {
                font-size: 1.1rem;
                word-break: break-word;
            }
            .viewer-content {
                padding: 15px;
            }
            table {
                font-size: 0.75rem;
            }
            th, td {
                padding: 8px 6px;
            }
            .document-content {
                font-size: 0.95rem;
            }
            .download-btn {
                width: 100%;
                justify-content: center;
            }
        }
        @media (max-width: 480px) {
            .viewer-header h1 {
                font-size: 1rem;
            }
            .viewer-content {
                padding: 10px;
            }
            table {
                font-size: 0.7rem;
                min-width: 100%;
            }
            th, td {
                padding: 6px 4px;
            }
            .document-content {
                font-size: 0.9rem;
            }
            .loading, .error, .ppt-message {
                padding: 30px 15px;
            }
            .loading i, .error i, .ppt-message i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <div class="viewer-header">
            <h1>
                <i class="fas fa-file-<?= $extension === 'docx' || $extension === 'doc' ? 'word' : ($extension === 'xlsx' || $extension === 'xls' ? 'excel' : 'powerpoint') ?>"></i>
                <?= htmlspecialchars(basename($filePath)) ?>
            </h1>
            <a href="<?= htmlspecialchars($filePath) ?>" download class="download-btn">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
        <div class="viewer-content" id="content">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p style="margin-top: 20px; font-size: 1.1rem;">Loading document...</p>
            </div>
        </div>
    </div>

    <script>
        const filePath = '<?= addslashes($filePath) ?>';
        const extension = '<?= $extension ?>';
        const contentDiv = document.getElementById('content');

        // Function to show error
        function showError(message) {
            contentDiv.innerHTML = `
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <h2>Unable to Display Document</h2>
                    <p style="margin-top: 15px;">${message}</p>
                    <p style="margin-top: 10px; color: #64748b;">Please download the file to view its contents.</p>
                </div>
            `;
        }

        // Load and display the document
        fetch(filePath)
            .then(response => {
                if (!response.ok) throw new Error('File not found');
                return response.arrayBuffer();
            })
            .then(arrayBuffer => {
                if (extension === 'docx' || extension === 'doc') {
                    // Word Document
                    if (typeof mammoth !== 'undefined') {
                        mammoth.convertToHtml({arrayBuffer: arrayBuffer})
                            .then(result => {
                                contentDiv.innerHTML = '<div class="document-content">' + result.value + '</div>';
                                if (result.messages && result.messages.length > 0) {
                                    console.log('Conversion messages:', result.messages);
                                }
                            })
                            .catch(err => {
                                showError('Error reading Word document: ' + err.message);
                            });
                    } else {
                        showError('Word viewer library not loaded');
                    }
                } else if (extension === 'xlsx' || extension === 'xls') {
                    // Excel Spreadsheet
                    if (typeof XLSX !== 'undefined') {
                        try {
                            const workbook = XLSX.read(arrayBuffer, {type: 'array'});
                            let html = '';
                            
                            workbook.SheetNames.forEach((sheetName, index) => {
                                const worksheet = workbook.Sheets[sheetName];
                                html += '<h2 style="color: #217346; margin: 30px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #217346;">';
                                html += '<i class="fas fa-table"></i> ' + sheetName + '</h2>';
                                html += '<div class="table-wrapper">' + XLSX.utils.sheet_to_html(worksheet, {editable: false}) + '</div>';
                            });
                            
                            contentDiv.innerHTML = html || '<div class="error"><p>No data found in spreadsheet</p></div>';
                        } catch (err) {
                            showError('Error reading Excel file: ' + err.message);
                        }
                    } else {
                        showError('Excel viewer library not loaded');
                    }
                } else if (extension === 'pptx' || extension === 'ppt') {
                    // PowerPoint - Show download message
                    contentDiv.innerHTML = `
                        <div class="ppt-message">
                            <i class="fas fa-file-powerpoint"></i>
                            <h2>PowerPoint Presentation</h2>
                            <p>
                                PowerPoint presentations require Microsoft PowerPoint or compatible software to view properly. 
                                Please download the file to view the full presentation with all formatting, images, and animations.
                            </p>
                            <a href="${filePath}" download class="download-btn" style="display: inline-flex; text-decoration: none;">
                                <i class="fas fa-download"></i> Download Presentation
                            </a>
                        </div>
                    `;
                } else {
                    showError('Unsupported file format: ' + extension);
                }
            })
            .catch(error => {
                showError(error.message);
            });
    </script>
</body>
</html>
