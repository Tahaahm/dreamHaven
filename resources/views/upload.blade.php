<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Property Listing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eef2f3, #d9e4f5);
            margin: 0;
            padding: 40px 0;
        }

        form {
            display: flex;
            justify-content: center;
        }

        .form-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 16px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease-in-out;
        }

        h2 {
            text-align: center;
            color: #303b97;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .progress {
            height: 8px;
            background: #eee;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 25px;
        }

        .progress-bar {
            height: 100%;
            background: #303b97;
            width: 25%;
            transition: width 0.4s ease-in-out;
        }

        fieldset {
            border: none;
            margin-bottom: 20px;
            background-color: #f8f9ff;
            padding: 15px 20px;
            border-radius: 10px;
        }

        legend {
            font-weight: bold;
            color: #303b97;
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin: 8px 0 4px;
            color: #555;
            font-size: 14px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            background: #fff;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .file-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #303b97;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            color: #303b97;
            background: #f4f6ff;
            transition: background 0.3s;
        }

        .file-upload:hover {
            background: #e9edff;
        }

        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview img {
            width: 90px;
            height: 90px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #303b97;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            background: #303b97;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            font-size: 15px;
            cursor: pointer;
            flex: 1;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #232a6b;
        }

        .back-btn {
            background: #e0e3f7;
            color: #303b97;
        }

        .back-btn:hover {
            background: #d0d5f0;
        }

        .hidden {
            display: none;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(10px);}
            to {opacity: 1; transform: translateY(0);}
        }

        @media (max-width: 600px) {
            .form-container {
                padding: 20px;
            }
            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<form id="propertyForm">
  @csrf
  <div class="form-container">

    <!-- Page 1 -->
    <div id="page1">
      <fieldset>
        <legend>Owner Information</legend>
       
     
      </fieldset>

      <fieldset>
        <legend>Property Type</legend>
        <label>Category</label>
        <input type="text" id="category" name="type_category" required minlength="2" />
      </fieldset>

      <div class="button-group">
        <button type="button" onclick="nextPage(2)">Next</button>
      </div>
    </div>

    <!-- Page 2 -->
    <div id="page2" class="hidden">
      <fieldset>
        <legend>Name</legend>
        <label>Name (English)</label>
        <input type="text" id="name_en" />
        <label>Name (Arabic)</label>
        <input type="text" id="name_ar" />
        <label>Name (Kurdish)</label>
        <input type="text" id="name_ku" />
      </fieldset>

      <fieldset>
        <legend>Description</legend>
        <label>Description (English)</label>
        <textarea id="description_en"></textarea>
        <label>Description (Arabic)</label>
        <textarea id="description_ar"></textarea>
        <label>Description (Kurdish)</label>
        <textarea id="description_ku"></textarea>
      </fieldset>

      <div class="button-group">
        <button type="button" onclick="prevPage(1)">Back</button>
        <button type="button" onclick="nextPage(3)">Next</button>
      </div>
    </div>

    <!-- Page 3 -->
    <div id="page3" class="hidden">
      <fieldset>
        <legend>Price & Listing</legend>
        <label>Price (IQD)</label>
        <input type="number" id="price_iqd" min="1" />
        <label>Price (USD)</label>
        <input type="number" id="price_usd" min="1" />
        <label>Listing Type</label>
        <select id="listing_type">
          <option value="rent">Rent</option>
          <option value="sell">Sell</option>
        </select>
      </fieldset>

      <fieldset>
        <legend>Rooms</legend>
        <label>Bedrooms</label>
        <input type="number" id="bedroom_count" min="0" />
        <label>Bathrooms</label>
        <input type="number" id="bathroom_count" min="0" />
      </fieldset>

<!-- Property details -->
<label>Area (m²)</label>
<input type="number" id="area" min="1" required />

<label>Furnished?</label>
<select id="furnished">
  <option value="1">Yes</option>
  <option value="0">No</option>
</select>

<label>Rental Period</label>
<select id="rental_period">
  <option value="">Select (only for rent)</option>
  <option value="monthly">Monthly</option>
  <option value="yearly">Yearly</option>
</select>


      <div class="button-group">
        <button type="button" onclick="prevPage(2)">Back</button>
        <button type="button" onclick="nextPage(4)">Next</button>
      </div>
    </div>

    <!-- Page 4 -->
    <div id="page4" class="hidden">
      <fieldset>
        <legend>Location & Features</legend>
        <label>Latitude</label>
        <input type="number" id="lat" step="any" />
        <label>Longitude</label>
        <input type="number" id="lng" step="any" />
        <label>City</label>
        <input type="text" id="city_en" />
        <label>Features (comma separated)</label>
        <input type="text" id="features" />
      </fieldset>

      <fieldset>
        <legend>Images</legend>
        <input type="file" id="imageInput" multiple />
        <div id="imagePreview"></div>
      </fieldset>

      <div class="button-group">
        <button type="button" onclick="prevPage(3)">Back</button>
        <button type="button" onclick="submitProperty()">Submit Property</button>
      </div>
    </div>

  </div>
</form>



<script>
const csrfToken = "{{ csrf_token() }}";

function nextPage(n) {
    document.querySelectorAll('.form-container > div').forEach(d => d.classList.add('hidden'));
    document.getElementById('page' + n).classList.remove('hidden');
}

function prevPage(n) {
    nextPage(n);
}

// Image preview
document.getElementById('imageInput').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    for (const file of this.files) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.width = '100px';
        img.style.margin = '5px';
        preview.appendChild(img);
    }
});

// Upload images
async function uploadImages(files) {
    const formData = new FormData();
    for (const file of files) formData.append('images[]', file);

    const res = await fetch('/upload-images', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });

    const data = await res.json();
    return Array.isArray(data.urls) ? data.urls : [];
}

// Submit property
async function submitProperty() {
    try {
        const files = document.getElementById('imageInput').files;
        const imageUrls = await uploadImages(files);

        // Required numeric/boolean fields with defaults
        const area = parseFloat(document.getElementById('area').value) || 1;
        const furnished = document.getElementById('furnished').value === '1';
        const listingType = document.getElementById('listing_type').value;
        const rentalPeriod = listingType === 'rent' 
            ? document.getElementById('rental_period').value || 'monthly' 
            : null;

        // Ensure arrays/objects are correct
        const data = {
            owner_id: @json(auth()->id()),
            owner_type: "User",
            name: {
                en: document.getElementById('name_en').value || '',
                ar: document.getElementById('name_ar').value || '',
                ku: document.getElementById('name_ku').value || ''
            },
            description: {
                en: document.getElementById('description_en').value || '',
                ar: document.getElementById('description_ar').value || '',
                ku: document.getElementById('description_ku').value || ''
            },
            type: { category: document.getElementById('category').value || '' },
            price: {
                iqd: parseInt(document.getElementById('price_iqd').value) || 1,
                usd: parseInt(document.getElementById('price_usd').value) || 1
            },
            listing_type: listingType,
            rental_period: rentalPeriod,
            area: area,
            furnished: furnished,
            rooms: {
                bedroom: { count: parseInt(document.getElementById('bedroom_count').value) || 0 },
                bathroom: { count: parseInt(document.getElementById('bathroom_count').value) || 0 }
            },
            locations: [{
                lat: parseFloat(document.getElementById('lat').value) || 0,
                lng: parseFloat(document.getElementById('lng').value) || 0,
                type: "default"
            }],
            address_details: { city: { en: document.getElementById('city_en').value || '' } },
            features: (document.getElementById('features').value || '')
                        .split(',')
                        .map(f => f.trim())
                        .filter(f => f.length > 0),
            images: imageUrls
        };

        const res = await fetch('/v1/api/properties/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        });

        let result;
        try {
            result = await res.json();
        } catch {
            const text = await res.text();
            console.error('Server returned non-JSON:', text);
            alert('Server error, check console.');
            return;
        }

        if (res.ok && result.status) {
            alert('Property uploaded successfully!');
        } else {
            alert('Error: ' + JSON.stringify(result.data || result));
        }

    } catch (err) {
        console.error(err);
        alert('Something went wrong: ' + err.message);
    }
}
</script>



</body>
</html>
