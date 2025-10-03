<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Property Listing Form</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('../css/uploadStyle.css') }}">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

    .unique-header {
    position  : fixed;
    height    : 80px;
    width     : 100%;
    z-index   : 100;
    padding   : 0 20px;
    background: #303b97;
    /* Ensure a background color if needed */
}

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 100px 20px 20px;
            text-align: center;
        }

        .property-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .property-form div {
            margin-bottom: 20px;
        }

        .property-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }

        .property-form input,
        .property-form textarea,
        .property-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        .property-form input:focus,
        .property-form textarea:focus,
        .property-form select:focus {
            border-color: #1a1a2e;
            outline: none;
        }

        .drop-zone {
            grid-column: span 2;
            padding: 30px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            background: #fafafa;
            text-align: center;
            color: #888;
            font-size: 16px;
            transition: background-color 0.3s ease;
            position: relative;
        }

        .drop-zone:hover {
            background-color: #f4f4f9;
        }

        .drop-zone.highlight {
            background-color: #e3f2fd;
            border-color: #1a1a2e;
        }

        .preview {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 10px;
        }

        .preview-item {
            position: relative;
            margin: 10px;
        }

        .preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .remove-button {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff5a5f;
            color: #fff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .remove-button:hover {
            background: #e60000;
        }

        #submitBtn {
            grid-column: span 2;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        #submitBtn:hover {
            background: linear-gradient(135deg, #16213e 0%, #0f3460 100%);
        }

        /* Alert messages */
        .alert {
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            text-align: left;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

    </style>
</head>
<body class="black-navbar">
@include('layouts.sidebar')

    <div class="container">
       
        <div id="error-messages"></div>
        <form action="{{ route('property.upload') }}" method="post" class="property-form" id="propertyForm" enctype="multipart/form-data">
            @csrf

            <div>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required />
            </div>


            <div>
    <label for="address">Address:</label>
    <input type="text" id="address" name="address"  required />
</div>


            <div>
    <label for="location">Location:</label>
    <input type="text" id="location" name="location" placeholder="36°07'27.3\"N 44°01'15.8\"E" required />
</div>



            <div>
                <label for="property_type">property type:</label>
                <select id="property_type" name="property_type" required>
                    <option value="house">House</option>
                    <option value="property">Property</option>
                    <option value="apartment">Apartment</option>
                </select>
            </div>

            <div>
                <label for="listing_type">Type of Rent:</label>
                <select id="listing_type" name="listing_type" required>
                    <option value="sell">Sell</option>
                    <option value="rent">Rent</option>
                </select>
            </div>

            <div>
                <label for="bedrooms">Bedrooms:</label>
                <input type="number" id="bedrooms" name="bedrooms" required />
            </div>

            <div>
                <label for="bathrooms">Bathrooms:</label>
                <input type="number" id="bathrooms" name="bathrooms" required />
            </div>

            <div>
                <label for="area">Square Footage:</label>
                <input type="number" id="area" name="area" required />
            </div>

        

            <div>
                <label for="flooring">Flooring:</label>
                <input type="text" id="flooring" name="flooring" required />
            </div>

            <div>
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" required />
            </div>

            <div>
                <label for="additional_information">Additional Information:</label>
                <textarea id="additional_information" name="description"></textarea>
            </div>

            <div id="preview" class="preview"></div>
            <div id="drop-zone" class="drop-zone">
                Drag and drop photos here
                <input type="file" id="fileInputButton" name="images[]" multiple />
            </div>

            <button type="submit" id="submitBtn">Submit</button>
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
        </form>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <script>
        const dropZone = document.getElementById("drop-zone");
        const preview = document.getElementById("preview");
        const fileInputButton = document.getElementById("fileInputButton");
        const errorMessages = document.getElementById("error-messages");

        let totalFileSize = 0;

        dropZone.addEventListener("dragover", handleDragOver, false);
        dropZone.addEventListener("dragleave", handleDragLeave, false);
        dropZone.addEventListener("drop", handleDrop, false);
        fileInputButton.addEventListener("change", handleFiles, false);

        function handleDragOver(event) {
            event.preventDefault();
            dropZone.classList.add("highlight");
        }

        function handleDragLeave(event) {
            event.preventDefault();
            dropZone.classList.remove("highlight");
        }

        function handleDrop(event) {
            event.preventDefault();
            dropZone.classList.remove("highlight");

            const files = Array.from(event.dataTransfer.files);

            files.forEach(function (file) {
                if (checkTotalFileSize(file.size)) {
                    totalFileSize += file.size;
                    previewFile(file);
                } else {
                    displayErrorMessage("File size exceeds the limit (20 MB). Please choose smaller files.");
                }
            });
        }

        function handleFiles(event) {
            const files = Array.from(event.target.files);

            files.forEach(function (file) {
                if (checkTotalFileSize(file.size)) {
                    totalFileSize += file.size;
                    previewFile(file);
                } else {
                    displayErrorMessage("File size exceeds the limit (20 MB). Please choose smaller files.");
                }
            });
        }

        function checkTotalFileSize(fileSize) {
            const maxSize = 20 * 1024 * 1024; // 20 MB
            return totalFileSize + fileSize <= maxSize;
        }

        function removeFile(fileSize) {
            totalFileSize -= fileSize;
        }

        function previewFile(file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const imgContainer = document.createElement("div");
                imgContainer.classList.add("preview-item");

                const img = document.createElement("img");
                img.src = e.target.result;

                const removeButton = document.createElement("button");
                removeButton.innerHTML = "x";
                removeButton.classList.add("remove-button");
                removeButton.addEventListener("click", function () {
                    preview.removeChild(imgContainer);
                    removeFile(file.size);
                });

                imgContainer.appendChild(img);
                imgContainer.appendChild(removeButton);
                preview.appendChild(imgContainer);
            };

            reader.readAsDataURL(file);
        }

        function displayErrorMessage(message) {
            const errorMessageElement = document.createElement("div");
            errorMessageElement.textContent = message;
            errorMessageElement.style.whiteSpace = "pre"; // Preserve whitespace
            errorMessageElement.style.color = "red";
            errorMessageElement.style.display = "inline-block"; // Display the message in a single line
            errorMessages.appendChild(errorMessageElement);
        }
    </script>
</body>
</html>
