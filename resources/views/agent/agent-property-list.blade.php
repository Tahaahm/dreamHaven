<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorting List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('../css/list-style.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.4.6"></script>
    <style>
        /* Add your CSS here */
        body {
            background-color: #555555;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #eaeced;
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
        .allin {
            margin-left: 85px;
          
        }
        .allin {
    padding-top: 10px;
}
        .container {
            margin-left: 0px;
            width: 100%;
            max-width: 2700px;
        }

        .container ul {
         
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
        }

     .item-container {
    position                  : relative;
    overflow                  : hidden;
    margin-bottom             : 20px;
    margin-right              : 10px;
    margin-left               : 5px;
    border                    : none;
    border-top-right-radius   : 15px;
    border-top-left-radius    : 15px;
    border-bottom-left-radius : 15px;
    border-bottom-right-radius: 15px;
    background-color          : #fff;
    width                     : calc(27% - 50px);
    display                   : flex;
    flex-direction            : column;
    justify-content           : flex-start;
    align-items               : center;
    transition                : all ease-in-out 0.3s;
    box-shadow                : 0 2px 6px rgba(0, 0, 0, 0.1);
}

.background-image-container {
    position: relative;
    width   : 100%;
    height  : 400px;
    /* fixed height for testing, adjust as needed */
    overflow: hidden;
}

.background-image-container .background-image {
    position           : absolute;
    top                : 0;
    left               : 0;
    right              : 0;
    /* ensure it stretches fully */
    bottom             : 0;
    /* ensure it stretches fully */
    width              : 100%;
    height             : 100%;
    background-size    : cover;
    /* covers the container */
    background-position: center;
    /* center the image */
    background-repeat  : no-repeat;
    /* prevent tiling */
    transition         : transform 0.3s, opacity 0.3s;
    opacity            : 0;
}

.background-image-container .background-image.active {
    opacity: 1;
}

.item-container:hover .background-image {
    transform: scale(1.05);
    /* small zoom on hover */
}


        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.1);
            color: white;
            font-size: 16px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background 0.3s;
            z-index: 2;
        }

        .arrow.prev {
            left: 10px;
        }

        .arrow.next {
            right: 10px;
        }

        .item-price {
            font-family: 'Poppins', sans-serif;
            margin-top: 10px;
            font-weight: 600;
            font-size: 19px;
            color: #353839;
        }

        .detail-of-home {
           
            padding: 5px 10px;
            background: #f5f5f5;
            color: #353839;
            text-align: left;
            height: 147px;
            transition: all 0.3s ease-in-out;
            box-sizing: border-box;
            width: 100%;
        }

        .detail-of-home .title {
            margin-top: -2px;
    font-size: 1.2em;
    font-weight: bold;
  
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    max-width: 99%;
    vertical-align: top;
}

.detail-of-home .title.shrink {
    font-size: 1.2em; /* Default size */
    transform: scale(1); /* Default scale */
    transform-origin: left;
    transition: transform 0.2s ease-out;
}


        .detail-of-home .item-location {
            font-size: 0.9em;
            color: #353839;
            margin-bottom: 5px;
        }




        .detail-of-home .item-info {
            margin-right: 10px;
    padding-top: 10px;
    display: flex;
    flex-wrap: wrap; /* Allow wrapping if needed */
    gap: 17px; /* Reduced gap */
    margin-top: 0px;
}

.detail-of-home .item-info span {
    width: 40px;
    font-size: 16px;
    display: flex;
    align-items: center;
    white-space: nowrap; /* Prevent line breaks */
}

.detail-of-home .item-info span i {
    margin-right: 5px;
}





        .search-sort-container {
            margin-left: 15px;
            padding: 25px;
            background: #fff;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 230px;
        }

        .search-sort-container h3 {
            margin: 0;
            margin-bottom: 5px;
            color: #353839;
            font-size: 18px;
            font-weight: bold;
        }

        .dropdown-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .filter-dropdown, .search-input {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            width: 100%;
        }

        .switch-button-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .switch-text {
            font-size: 12px;
            font-weight: bold;
        }

        .toggle-switch {
            position: relative;
            width: 60px;
            height: 24px;
        }

        .toggle-switch input {
            display: none;
        }

        .slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #000066;
            transition: 0.4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 1px;
            bottom: 2px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #000066;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .search-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .search-bar input[type="text"] {
            width: 200px;
            height: 40px;
            padding: 0 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-bar button {
            position: absolute;
            right: 0;
            height: 40px;
            border: none;
            background: #303b97;
            color: #fff;
            font-size: 14px;
            padding: 0 15px;
            cursor: pointer;
            transition: background 0.3s;
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .search-bar button:hover {
            background: #202b77;
        }
        
        #no-products-message {
            display: none;
            margin-left: 550px;
            text-align: center;
            padding: 5px;
            font-size: 1rem;
            color: #ff0000;
            background-color: #f9f9f9;
            border: 2px dashed #ff0000;
            border-radius: 10px;
            margin-top: 70px;
        }

        /* Edit button styles */

.edit-button {
    position: absolute;
    top: 10px;
    right: 10px; /* Adjust to position the button correctly */
    background-color: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 3px;
    padding: 2px 5px; /* Reduced padding to make it smaller */
    cursor: pointer;
    z-index: 10; /* Ensure the button is above other content */
    transition: background-color 0.3s ease;
    width: auto; /* Adjust width to fit text */
    max-width: 40px; /* Set a maximum width to prevent the button from stretching too much */
    text-align: center;
    white-space: nowrap; /* Prevent the text from wrapping */
}

.edit-button:hover {
    background-color: rgba(255, 255, 255, 1);
}



.menu-container {
    position: absolute;
    top: 10px;
    right: 10px;
}

.menu-btn {
    background: rgba(0, 0, 0, 0.5);
    border: none;
    color: white;
    padding: 6px 8px;
    border-radius: 50%;
    cursor: pointer;
}

.menu-btn:hover {
    background: rgba(0, 0, 0, 0.7);
}

.menu-dropdown {
    display: none;
    position: absolute;
    top: 35px;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    z-index: 10;
}

.menu-item {
    display: block;
    padding: 10px 15px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    border-bottom: 1px solid #eee;
}

.menu-item:hover {
    background: #f4f4f4;
}

.delete-btn {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 10px 15px;
    color: #d9534f;
    cursor: pointer;
}

.delete-btn:hover {
    background: #fceaea;
}

.menu-container.active .menu-dropdown {
    display: block;
}

    </style>
</head>
<body class="custom-navbar-color">
    @include('layouts.sidebar')

    <div class="allin">
        <div class="container">
            <ul id="product-list">
                @forelse ($properties as $property)
                    @php
                        // Safely decode JSON or use arrays
                        $images = is_array($property->images)
                            ? $property->images
                            : (json_decode($property->images, true) ?? []);
                        $price = is_array($property->price)
                            ? $property->price
                            : (json_decode($property->price, true) ?? [$property->price]);
                    @endphp

                    <li class="item-container"
                        data-type="{{ is_array($property->type) ? implode(', ', $property->type) : $property->type }}"
                        data-category="{{ is_array($property->category) ? implode(', ', $property->category) : $property->category }}">

                        <div class="background-image-container">
                            @if(!empty($images))
                                @foreach($images as $index => $photo)
                                    <div class="background-image{{ $index == 0 ? ' active' : '' }}"
                                        style="background-image: url('{{ asset($photo) }}');"></div>
                                @endforeach
                            @else
                                <div class="background-image active" style="background-image: url('{{ asset('property_images/default.jpg') }}');"></div>
                            @endif

                            <!-- Menu Button -->
                            <div class="menu-container">
                                <button class="menu-btn"><i class="fas fa-ellipsis-v"></i></button>
                                <div class="menu-dropdown">
                                    <a href="{{ route('property.edit', ['property_id' => $property->id]) }}" class="menu-item">Edit</a>

                                    <form action="{{ route('property.delete', ['property_id' => $property->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this property?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="menu-item delete-btn">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Make only this area clickable -->
                        <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}"
                            class="details-link" title="More Details">
                            <div class="detail-of-home">
                                <div class="title">{{ $property->title ?? 'Untitled Property' }}</div>
                                <div class="item-location">
                                    <i class="fas fa-map-marker-alt"></i> {{ $property->address ?? 'No address' }}
                                </div>

                                <div class="item-price">
                                    @if(is_array($price))
                                        ${{ implode(' - $', array_filter($price)) }}
                                    @else
                                        ${{ $price ?? 'N/A' }}
                                    @endif
                                </div>

                                <div class="item-info">
                                    <span><i class="fas fa-bed"></i> {{ $property->bedrooms ?? 0 }} Bed</span>
                                    <span><i class="fas fa-bath"></i> {{ $property->bathrooms ?? 0 }} Bath</span>
                                    <span><i class="fas fa-ruler-combined"></i> {{ $property->area ?? 0 }} m²</span>
                                </div>
                            </div>
                        </a>
                    </li>
                @empty
                    <p id="no-products-message">No properties found.</p>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.4.6/dist/fuse.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/isotope.pkgd/3.0.6/isotope.pkgd.min.js"></script>

    <script>
        document.addEventListener('click', function(e) {
            // Close all dropdowns
            document.querySelectorAll('.menu-container').forEach(el => {
                if (!el.contains(e.target)) el.classList.remove('active');
            });

            // Toggle clicked one
            if (e.target.closest('.menu-btn')) {
                const menu = e.target.closest('.menu-container');
                menu.classList.toggle('active');
            }
        });
    </script>
</body>


</html>
