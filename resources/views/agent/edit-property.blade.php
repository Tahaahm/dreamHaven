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
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
        }

        .highlight {
            border-color: #333;
        }

        .preview-item {
            display: inline-block;
            margin: 10px;
            position: relative;
        }

        .preview-item img {
            max-width: 150px;
            max-height: 150px;
            display: block;
        }
.photo-container img{
    max-width: 150px;
            max-height: 150px;
            display: block;
}
        .remove-button {
            position: absolute;
            top: 0;
            right: 0;
            background: red;
            color: white;
            border: none;
            padding: 5px;
            cursor: pointer;
        }

        .photo-container {
    position: relative;
    display: inline-block;
}

.property-image {
    display: block;
    width: 100%; /* Adjust based on your needs */
    height: auto; /* Adjust based on your needs */
}

.remove-button-container {
    position: absolute;
    top: 0;
    right: 0;
    margin: 10px;
}

.remove-image-form {
    margin: 0;
}

.btn-danger {
    position: absolute;
    top: -10px;
    right: -10px;
    background: red;
    color: white;
    border: none;
    padding-top:1px ;
    padding-bottom:1px ;
    padding-left: 3px;
    padding-right: 3px;
  
    
    cursor: pointer;
}

        .alert-success, .alert-danger {
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
        }

        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
        }
        #submitBtn {
    background   :#303b97;
    color        : #ffffff;
    padding      : 15px;
    padding-left : 25px;
    padding-right: 25px;
    border       : none;
    border-radius: 5px;
    cursor       : pointer;
    position     : absolute;
    bottom       : 0px;
    left         : 50%;
    transform    : translateX(-50%);
    box-shadow   : 7px 7px 45px #777777, -2px -2px 65px #ffffff;
    transition   : 0.9s;
}

#submitBtn:hover {
    background: radial-gradient(circle, rgba(64, 170, 250, 1) 7%, rgba(19, 67, 113, 1) 84%);
    color     : #ffffff;
    font-size : 14px;
}
    </style>
   
</head>
<body class="black-navbar">
@include('layouts.sidebar')

<div class="container">
    <div id="error-messages"></div>
    <form action="{{ route('property.update', $property->property_id) }}" method="post" class="property-form" id="propertyForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Form fields for property details -->
        <div>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="{{ old('title', $property->title) }}" required />
        </div>

        <div>
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="{{ old('address', $property->address) }}" required />
        </div>

        <div>
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="{{ old('location', $property->location) }}" required />
        </div>
        <!-- Select dropdown for property type -->
        <div>
            <label for="type">Type:</label>
            <select id="type" name="type" required>
                <option value="house" {{ old('type', $property->type) === 'house' ? 'selected' : '' }}>House</option>
                <option value="property" {{ old('type', $property->type) === 'property' ? 'selected' : '' }}>Property</option>
                <option value="apartment" {{ old('type', $property->type) === 'apartment' ? 'selected' : '' }}>Apartment</option>
            </select>
        </div>

        <!-- Type of Rent -->
        <div>
            <label for="type_of_rent">Type of Rent:</label>
            <select id="type_of_rent" name="type_of_rent" required>
                <option value="sell" {{ old('type_of_rent', $property->type_of_rent) === 'sell' ? 'selected' : '' }}>Sell</option>
                <option value="rent" {{ old('type_of_rent', $property->type_of_rent) === 'rent' ? 'selected' : '' }}>Rent</option>
            </select>
        </div>

        <!-- Additional fields -->
        <div><label for="bedrooms">Bedrooms:</label><input type="number" id="bedrooms" name="bedrooms" value="{{ old('bedrooms', $property->bedrooms) }}" required /></div>
        <div><label for="bathrooms">Bathrooms:</label><input type="number" id="bathrooms" name="bathrooms" value="{{ old('bathrooms', $property->bathrooms) }}" required /></div>
        <div>
    <label for="area">Square Footage:</label>
    <input type="number" id="area" name="area" value="{{ old('area', $property->area) }}" required />
</div>

        <div><label for="flooring">Flooring:</label><input type="text" id="flooring" name="flooring" value="{{ old('flooring', $property->flooring) }}" required /></div>
        <div><label for="price">Price:</label><input type="number" id="price" name="price" value="{{ old('price', $property->price) }}" required /></div>
        <div>
            <label for="additional_information">Additional Information:</label>
            <textarea id="additional_information" name="description">{{ old('description', $property->description) }}</textarea>
        </div>

        <!-- Hidden input to store removed image paths -->
        <input type="hidden" id="removed_photos" name="removed_photos[]">

        <!-- Drop zone for images -->
        <div id="drop-zone" class="drop-zone">
            Drag and drop photos here
            <input type="file" id="fileInputButton" name="images[]" multiple />
        </div>
        <div id="preview" class="preview"></div>

        <button type="submit" id="submitBtn">Submit</button>
    </form>

    <!-- Display existing images -->
    @php
        $images = is_string($property->images) ? json_decode($property->images, true) : $property->images;
    @endphp
    @foreach ($images as $photo)
        <div class="photo-container" data-photo="{{ $photo }}">
            <img src="{{ asset($photo) }}" alt="Property Image" class="property-image">
            <div class="remove-button-container">
            <form action="{{ route('property.removeImage', ['property_id' => $property->property_id]) }}" method="POST" class="remove-image-form">
    @csrf
    <input type="hidden" name="photo_path" value="{{ $photo }}">
    <button type="submit" class="btn btn-danger">X</button>
                </form>
            </div>
        </div>
    @endforeach

    <!-- Display success or error messages -->
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
// JavaScript to handle file preview, drag/drop, and image removal
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById("drop-zone");
    const preview = document.getElementById("preview");
    const fileInputButton = document.getElementById("fileInputButton");
    const errorMessages = document.getElementById("error-messages");
    const removedPhotosInput = document.getElementById("removed_photos");

    let totalFileSize = 0;
    let removedPhotos = [];

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
        files.forEach(file => processFile(file));
    }

    function handleFiles(event) {
        const files = Array.from(event.target.files);
        files.forEach(file => processFile(file));
    }

    function processFile(file) {
        if (checkTotalFileSize(file.size)) {
            totalFileSize += file.size;
            previewFile(file);
        } else {
            displayErrorMessage("File size exceeds the limit (20 MB). Please choose smaller files.");
        }
    }

    function checkTotalFileSize(fileSize) {
        const maxSize = 20 * 1024 * 1024; // 20 MB
        return totalFileSize + fileSize <= maxSize;
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
            removeButton.addEventListener("click", () => {
                preview.removeChild(imgContainer);
                totalFileSize -= file.size;
            });

            imgContainer.appendChild(img);
            imgContainer.appendChild(removeButton);
            preview.appendChild(imgContainer);
        };
        reader.readAsDataURL(file);
    }

    function displayErrorMessage(message) {
        errorMessages.textContent = "";
        const errorMessageElement = document.createElement("div");
        errorMessageElement.textContent = message;
        errorMessageElement.style.color = "red";
        errorMessages.appendChild(errorMessageElement);
    }

    // Update hidden input with removed photos on delete
    document.querySelectorAll('.remove-image-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const photoPath = this.querySelector('input[name="photo_path"]').value;
            removedPhotos.push(photoPath);
            removedPhotosInput.value = JSON.stringify(removedPhotos);
            this.submit();
        });
    });
});
</script>
</body>
