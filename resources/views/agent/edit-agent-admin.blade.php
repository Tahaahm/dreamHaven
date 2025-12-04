<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Agent</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        img {
            max-width: 150px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
@include('layouts.sidebar')

<div class="container" style="max-width: 800px; margin-top: 40px;">
    <h1>Edit Agent</h1>

    @php
        // Avoid undefined variable error
        if (!isset($agent)) {
            $agent = null;
        }

        // Default profile image
        $defaultImage = asset('property_images/IMG_0697.JPG');
        $agentImage = isset($agent) && $agent && $agent->profile_image
            ? asset('storage/' . $agent->profile_image)
            : $defaultImage;
    @endphp

    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ $agentImage }}"
             alt="Agent Profile"
             style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #303b97;">
    </div>


<form action="{{ route('agent.updateProfile', ['id' => $agent->id]) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')



       <!-- Agent Name -->
<div style="margin-bottom: 15px;">
    <label for="agent_name" style="font-weight: bold;">Agent Name</label>
    <input type="text" id="agent_name" name="agent_name"
           value="{{ old('agent_name', $agent->agent_name ?? '') }}"
           class="form-control"
           style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
    @error('agent_name')
        <div style="color: red; font-size: 0.9rem;">{{ $message }}</div>
    @enderror
</div>

<!-- Email -->
<div style="margin-bottom: 15px;">
    <label for="primary_email" style="font-weight: bold;">Primary Email</label>
    <input type="email" id="primary_email" name="primary_email"
           value="{{ old('primary_email', $agent->primary_email ?? '') }}"
           class="form-control"
           style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
    @error('primary_email')
        <div style="color: red; font-size: 0.9rem;">{{ $message }}</div>
    @enderror
</div>

<!-- Phone -->
<div style="margin-bottom: 15px;">
    <label for="primary_phone" style="font-weight: bold;">Primary Phone</label>
    <input type="text" id="primary_phone" name="primary_phone"
           value="{{ old('primary_phone', $agent->primary_phone ?? '') }}"
           class="form-control"
           style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
    @error('primary_phone')
        <div style="color: red; font-size: 0.9rem;">{{ $message }}</div>
    @enderror
</div>

<!-- Type -->
<div style="margin-bottom: 15px;">
    <label for="type" style="font-weight: bold;">Agent Type</label>
    <input type="text" id="type" name="type"
           value="{{ old('type', $agent->type ?? '') }}"
           class="form-control"
           style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
    @error('type')
        <div style="color: red; font-size: 0.9rem;">{{ $message }}</div>
    @enderror
</div>

<!-- City -->
<div style="margin-bottom: 15px;">
    <label for="city" style="font-weight: bold;">City</label>
    <input type="text" id="city" name="city"
           value="{{ old('city', $agent->city ?? '') }}"
           class="form-control"
           style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
    @error('city')
        <div style="color: red; font-size: 0.9rem;">{{ $message }}</div>
    @enderror
</div>

<!-- Profile Image -->
<div style="margin-bottom: 20px;">
    <label for="profile_image" style="font-weight: bold;">Profile Image</label><br>
    <input type="file" id="profile_image" name="profile_image"
           accept="image/*"
           style="margin-top: 5px;">
    @if(isset($agent) && $agent->profile_image)
        <div style="margin-top: 10px;">
            <img src="{{ asset('storage/' . $agent->profile_image) }}"
                 alt="Profile Image"
                 style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #303b97;">
        </div>
    @endif
    @error('profile_image')
        <div style="color: red; font-size: 0.9rem;">{{ $message }}</div>
    @enderror
</div>

<!-- Submit Button -->
<button type="submit"
        style="background-color: #303b97; color: white; padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer;">
    Update Agent
</button>
  </form>
</div>
</body>


</html>
