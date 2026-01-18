/**
 * Dream Mulk - Location Selector Component
 * Fixed version with proper async initialization
 */

class LocationSelector {
    constructor(options = {}) {
        this.citySelectId = options.citySelectId || "city-select";
        this.areaSelectId = options.areaSelectId || "area-select";
        this.cityInputId = options.cityInputId || "city";
        this.districtInputId = options.districtInputId || "district";
        this.onCityChange = options.onCityChange || null;
        this.onAreaChange = options.onAreaChange || null;

        this.cities = [];
        this.currentCityId = options.selectedCityId || null;
        this.currentAreaId = options.selectedAreaId || null;

        // Track initialization state
        this.initialized = false;
        this.isLoading = false;
    }

    // Make init return a promise
    async init() {
        if (this.isLoading) return;
        this.isLoading = true;

        try {
            await this.loadCities();
            this.setupEventListeners();

            // If there's a selected city, load its areas
            if (this.currentCityId) {
                await this.loadAreas(this.currentCityId);
            }

            this.initialized = true;
        } catch (error) {
            console.error("Failed to initialize LocationSelector:", error);
            this.showError(
                "Failed to load location data. Please refresh the page.",
            );
        } finally {
            this.isLoading = false;
        }
    }

    async loadCities() {
        try {
            console.log("Fetching cities from API...");

            // FIX: Send proper Accept-Language header (just 'en', not 'en_US,en;q=0.9')
            const response = await fetch("/v1/api/location/branches", {
                headers: {
                    "Accept-Language": "en",
                },
            });

            if (!response.ok) {
                // Try to get error details
                let errorText = await response.text();
                console.error("Response status:", response.status);
                console.error("Response body:", errorText);
                throw new Error(
                    `HTTP error! status: ${response.status} - ${errorText}`,
                );
            }

            const result = await response.json();
            console.log("API Response:", result);

            if (result.success && result.data && Array.isArray(result.data)) {
                this.cities = result.data;
                console.log(`Loaded ${this.cities.length} cities`);
                this.populateCitySelect();
            } else {
                throw new Error("Invalid response format or no data");
            }
        } catch (error) {
            console.error("Error loading cities:", error);
            const citySelect = document.getElementById(this.citySelectId);
            if (citySelect) {
                citySelect.innerHTML =
                    '<option value="">Error: Server issue (Status 500)</option>';
            }

            // Show user-friendly error
            this.showError(
                "Unable to load cities. Please contact support or try again later.",
            );
            throw error;
        }
    }

    populateCitySelect() {
        const citySelect = document.getElementById(this.citySelectId);
        if (!citySelect) {
            console.error("City select element not found:", this.citySelectId);
            return;
        }

        console.log(
            "Populating city select with",
            this.cities.length,
            "cities",
        );

        // Clear existing options except the first placeholder
        citySelect.innerHTML = '<option value="">Select City</option>';

        if (this.cities.length === 0) {
            console.warn("No cities to populate");
            return;
        }

        // Sort cities alphabetically by English name
        const sortedCities = [...this.cities].sort((a, b) =>
            a.city_name_en.localeCompare(b.city_name_en),
        );

        sortedCities.forEach((city) => {
            const option = document.createElement("option");
            option.value = city.id;
            option.textContent = `${city.city_name_en} - ${city.city_name_ku} - ${city.city_name_ar}`;
            option.dataset.nameEn = city.city_name_en;
            option.dataset.nameKu = city.city_name_ku;
            option.dataset.nameAr = city.city_name_ar;

            if (city.id == this.currentCityId) {
                option.selected = true;
            }

            citySelect.appendChild(option);
        });

        console.log("City select populated successfully");
    }

    async loadAreas(cityId) {
        try {
            const areaSelect = document.getElementById(this.areaSelectId);
            if (!areaSelect) return;

            // Show loading state
            areaSelect.innerHTML = '<option value="">Loading areas...</option>';
            areaSelect.disabled = true;

            // FIX: Send proper Accept-Language header
            const response = await fetch(
                `/v1/api/location/branches/${cityId}/areas`,
                {
                    headers: {
                        "Accept-Language": "en",
                    },
                },
            );
            const result = await response.json();

            if (result.success && result.data) {
                this.populateAreaSelect(result.data);
            } else {
                throw new Error("Invalid response format");
            }
        } catch (error) {
            console.error("Error loading areas:", error);
            const areaSelect = document.getElementById(this.areaSelectId);
            if (areaSelect) {
                areaSelect.innerHTML =
                    '<option value="">Error loading areas</option>';
            }
        } finally {
            const areaSelect = document.getElementById(this.areaSelectId);
            if (areaSelect) {
                areaSelect.disabled = false;
            }
        }
    }

    populateAreaSelect(areas) {
        const areaSelect = document.getElementById(this.areaSelectId);
        if (!areaSelect) return;

        areaSelect.innerHTML = '<option value="">Select Area</option>';

        // Sort areas alphabetically by English name
        const sortedAreas = [...areas].sort((a, b) =>
            a.area_name_en.localeCompare(b.area_name_en),
        );

        sortedAreas.forEach((area) => {
            const option = document.createElement("option");
            option.value = area.id;
            option.textContent = `${area.area_name_en} - ${area.area_name_ku} - ${area.area_name_ar}`;
            option.dataset.nameEn = area.area_name_en;
            option.dataset.nameKu = area.area_name_ku;
            option.dataset.nameAr = area.area_name_ar;
            option.dataset.fullLocation = area.full_location;

            if (area.id == this.currentAreaId) {
                option.selected = true;
            }

            areaSelect.appendChild(option);
        });
    }

    setupEventListeners() {
        const citySelect = document.getElementById(this.citySelectId);
        const areaSelect = document.getElementById(this.areaSelectId);
        const cityInput = document.getElementById(this.cityInputId);
        const districtInput = document.getElementById(this.districtInputId);

        if (citySelect) {
            citySelect.addEventListener("change", async (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];

                if (e.target.value) {
                    // Update hidden city input
                    if (cityInput) {
                        cityInput.value = selectedOption.dataset.nameEn || "";
                    }

                    // Load areas for selected city
                    await this.loadAreas(e.target.value);

                    // Clear area selection
                    if (districtInput) {
                        districtInput.value = "";
                    }

                    // Call custom callback if provided
                    if (this.onCityChange) {
                        this.onCityChange({
                            id: e.target.value,
                            nameEn: selectedOption.dataset.nameEn,
                            nameKu: selectedOption.dataset.nameKu,
                            nameAr: selectedOption.dataset.nameAr,
                        });
                    }
                } else {
                    // Clear both inputs
                    if (cityInput) cityInput.value = "";
                    if (districtInput) districtInput.value = "";

                    // Clear area select
                    if (areaSelect) {
                        areaSelect.innerHTML =
                            '<option value="">Select City First</option>';
                        areaSelect.disabled = true;
                    }
                }
            });
        }

        if (areaSelect) {
            areaSelect.addEventListener("change", (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];

                if (e.target.value) {
                    // Update hidden district input
                    if (districtInput) {
                        districtInput.value =
                            selectedOption.dataset.nameEn || "";
                    }

                    // Call custom callback if provided
                    if (this.onAreaChange) {
                        this.onAreaChange({
                            id: e.target.value,
                            nameEn: selectedOption.dataset.nameEn,
                            nameKu: selectedOption.dataset.nameKu,
                            nameAr: selectedOption.dataset.nameAr,
                            fullLocation: selectedOption.dataset.fullLocation,
                        });
                    }
                } else {
                    if (districtInput) {
                        districtInput.value = "";
                    }
                }
            });
        }
    }

    // Method to set city by name (finds the city and sets it)
    async setCityByName(cityName) {
        if (!this.initialized) {
            console.error("LocationSelector not initialized yet");
            return false;
        }

        const city = this.cities.find(
            (c) =>
                c.city_name_en === cityName ||
                c.city_name_ku === cityName ||
                c.city_name_ar === cityName,
        );

        if (city) {
            const citySelect = document.getElementById(this.citySelectId);
            if (citySelect) {
                citySelect.value = city.id;

                // Update hidden input
                const cityInput = document.getElementById(this.cityInputId);
                if (cityInput) {
                    cityInput.value = city.city_name_en;
                }

                // Load areas
                await this.loadAreas(city.id);
                return true;
            }
        }

        console.warn(`City not found: ${cityName}`);
        return false;
    }

    // Method to set area by name (after city is set)
    setAreaByName(areaName) {
        if (!this.initialized) {
            console.error("LocationSelector not initialized yet");
            return false;
        }

        const areaSelect = document.getElementById(this.areaSelectId);
        if (!areaSelect) return false;

        // Find the matching option
        const options = Array.from(areaSelect.options);
        const matchingOption = options.find(
            (opt) =>
                opt.dataset.nameEn === areaName ||
                opt.dataset.nameKu === areaName ||
                opt.dataset.nameAr === areaName,
        );

        if (matchingOption) {
            areaSelect.value = matchingOption.value;

            // Update hidden input
            const districtInput = document.getElementById(this.districtInputId);
            if (districtInput) {
                districtInput.value = matchingOption.dataset.nameEn;
            }

            // Trigger change event
            areaSelect.dispatchEvent(new Event("change"));
            return true;
        }

        console.warn(`Area not found: ${areaName}`);
        return false;
    }

    showError(message) {
        console.error(message);

        // Create a toast/alert notification
        const alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-error";
        alertDiv.style.cssText =
            "position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 16px 24px; background: #fee2e2; border: 2px solid #ef4444; color: #dc2626; border-radius: 10px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);";
        alertDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-exclamation-circle"></i>
                <div>${message}</div>
            </div>
        `;

        document.body.appendChild(alertDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    getSelectedCity() {
        const citySelect = document.getElementById(this.citySelectId);
        if (!citySelect || !citySelect.value) return null;

        const selectedOption = citySelect.options[citySelect.selectedIndex];
        return {
            id: citySelect.value,
            nameEn: selectedOption.dataset.nameEn,
            nameKu: selectedOption.dataset.nameKu,
            nameAr: selectedOption.dataset.nameAr,
        };
    }

    getSelectedArea() {
        const areaSelect = document.getElementById(this.areaSelectId);
        if (!areaSelect || !areaSelect.value) return null;

        const selectedOption = areaSelect.options[areaSelect.selectedIndex];
        return {
            id: areaSelect.value,
            nameEn: selectedOption.dataset.nameEn,
            nameKu: selectedOption.dataset.nameKu,
            nameAr: selectedOption.dataset.nameAr,
            fullLocation: selectedOption.dataset.fullLocation,
        };
    }
}

window.LocationSelector = LocationSelector;
