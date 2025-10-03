<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
body {
    
    font-family     : Arial, Helvetica, sans-serif;
    margin          : 0;
    padding         : 0;
    background:#F5F7F8;
    width     : 100%;
    height:300vh;
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
    padding-top: 10px;
    /* Adjust padding-top to account for the height of the fixed navbar */
}

.unique-nav-item {
        margin-top: 20px;
        column-gap: 20px;
      }


      
        .content {
            border-radius: 15px;
            margin-left: 270px;
            padding: 20px;
            border: none;
        }
        .header {
            background: #f9fcff;
            padding: 15px;
            box-shadow: 0px 0px 10px rgba(133, 133, 133, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        .card {
            background: #f9fcff;
            border-radius: 15px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0px 0px 10px rgba(133, 133, 133, 0.1);
        }
        .referral-card {
            background: #f9fcff;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
            border: none;
            box-shadow: 1px 1px 10px rgba(0, 0, 0, 0.2);
        }
        .referral-body h6 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .referral-body p {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }
        .referral-body canvas {
            background: #f9fcff;

            border-radius: 15px;
            margin-top: 10px;
            border: none;
            box-shadow: 0px 0px 10px rgba(133, 133, 133, 0.1);
        }
        .card-body {
            background: #f9fcff;
            border-radius: 15px;
            padding: 20px;
            border: none;
            box-shadow: 0px 0px 10px rgba(133, 133, 133, 0.1);
        }
        .card-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .referral-body .card-price {
       
            color: #12be07;
        }
        .card-price {
            font-size: 22px;
            color: #12be07;
        }
        #market-cap-chart,
        #total-volume-chart,
        #total-supply-chart {
            background: #f9fcff;
            border-radius: 15px;
            height: 400px;
            width: 100%;
        }
        .row {
    align-items: center; /* Vertically center the content */
    justify-content: center; /* Horizontally center the content */
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}

    </style>
</head>
<body >

@include('layouts.sidebar')


<div class="allin">
   
    <div class="content">
        <div class="header">
            <h2>Dashboard</h2>
        </div>
        <div class="container-fluid">
            <!-- Cards Section -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-home"></i> Properties for Sale</h5>
                            <p class="card-text">684</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-building"></i> Properties for Rent</h5>
                            <p class="card-text">550</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users"></i> Total Customers</h5>
                            <p class="card-text">5684</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-city"></i> Properties for Cities</h5>
                            <p class="card-text">555</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Revenue Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-dollar-sign"></i> Total Revenue</h5>
                            <p class="card-price">$236,535</p>
                            <p class="card-text">0.8% Than Last Month</p>
                            <canvas id="revenue-chart"></canvas>
                        </div>
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-line"></i> Property Referrals</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="referral-card">
                                        <div class="referral-body">
                                            <h6><i class="fas fa-chart-pie"></i> Market Cap</h6>
                                            <p class="card-price">$3,00</p>
                                            <p>30% Last Week</p>
                                            <canvas id="market-cap-chart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="referral-card">
                                        <div class="referral-body">
                                            <h6><i class="fas fa-chart-bar"></i> Total Volume</h6>
                                            <p class="card-price">$1,00</p>
                                            <p>30% Last Week</p>
                                            <canvas id="total-volume-chart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="referral-card">
                                        <div class="referral-body">
                                            <h6><i class="fas fa-cubes"></i> Total Supply</h6>
                                            <p class="card-price">$3,00</p>
                                            <p>30% Last Week</p>
                                            <canvas id="total-supply-chart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> sales analytics</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-pie"></i> sales this month</h6>
                            <p class="card-price">$3,00</p>
                            <p>30% Last Week</p>
                            <canvas id="sales-this-month-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-bar"></i> sales last month</h6>
                            <p class="card-price">$1,00</p>
                            <p>30% Last Week</p>
                            <canvas id="sales-last-month-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> rental analytics</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-pie"></i> rental this month</h6>
                            <p class="card-price">$3,00</p>
                            <p>30% Last Week</p>
                            <canvas id="rental-this-month-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-bar"></i> rental last month</h6>
                            <p class="card-price">$1,00</p>
                            <p>30% Last Week</p>
                            <canvas id="rental-last-month-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> user engaement</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-pie"></i> new sing-ups</h6>
                            <p class="card-price">$3,00</p>
                            <p>30% Last Week</p>
                            <canvas id="new-sign-ups-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-bar"></i> active users</h6>
                            <p class="card-price">$1,00</p>
                            <p>30% Last Week</p>
                            <canvas id="active-users-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> expense analytics</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-pie"></i> monthly expenses</h6>
                            <p class="card-price">$3,00</p>
                            <p>30% Last Week</p>
                            <canvas id="monthly-expenses-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-bar"></i> annual expenses</h6>
                            <p class="card-price">$1,00</p>
                            <p>30% Last Week</p>
                            <canvas id="annual-expenses-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> revenue breakdown</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-pie"></i> product sales</h6>
                            <p class="card-price">$3,00</p>
                            <p>30% Last Week</p>
                            <canvas id="product-sales-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-bar"></i> service income</h6>
                            <p class="card-price">$1,00</p>
                            <p>30% Last Week</p>
                            <canvas id="service-income-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> geographical analytics</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-pie"></i> top region: </h6>
                            <p class="card-price">$3,00</p>
                            <p>30% Last Week</p>
                            <canvas id="top-region-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="referral-card">
                        <div class="referral-body">
                            <h6><i class="fas fa-chart-bar"></i> top city: </h6>
                            <p class="card-price">$1,00</p>
                            <p>30% Last Week</p>
                            <canvas id="top-city-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>
    </div>
    
    <script>
    // Function to create the total revenue chart
    function createRevenueChart() {
        var revenueData = [50, 250, 150, 200, 150, 100, 200, 250, 200, 350, 400, 450];
        var ctx = document.getElementById('revenue-chart').getContext('2d');
        var gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
        gradient.addColorStop(1, 'rgba(54, 162, 235, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    backgroundColor: gradient,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 500,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'nearest',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y + 'M';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: false
                }
            }
        });
    }

    // Function to create the other charts
    function createOtherCharts() {
        var createChartWithShadow = function(chartId, data, color) {
            var maxDataValue = Math.max(...data);
            var ctx = document.getElementById(chartId).getContext('2d');

            var gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
            gradient.addColorStop(1, 'rgba(54, 162, 235, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['J', 'F', 'M', 'A', 'M', 'J'],
                    datasets: [{
                        label: 'Data',
                        data: data,
                        backgroundColor: gradient,
                        borderColor: color,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            display: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                callback: function(value) { return value; },
                                max: maxDataValue * 1.2
                            }
                        },
                        x: {
                            display: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45, // Rotate labels to 45 degrees
                                minRotation: 45, // Minimum rotation angle
                                padding: 5, // Add padding
                                font: {
                                    size: 10 // Reduce font size
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'nearest',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return context.raw;
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        intersect: false
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: false,
                        onHover: function(event, chartElement) {
                            if (chartElement.length) {
                                var firstPoint = chartElement[0];
                                var label = myChart.data.labels[firstPoint.index];
                                var value = myChart.data.datasets[firstPoint.datasetIndex].data[firstPoint.index];
                                console.log(label, value);
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'shadowPlugin',
                    beforeDraw: function(chart) {
                        var ctx = chart.ctx;
                        chart.data.datasets.forEach(function(dataset, i) {
                            var meta = chart.getDatasetMeta(i);
                            ctx.save();
                            ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
                            ctx.shadowBlur = 15;
                            ctx.shadowOffsetX = 6;
                            ctx.shadowOffsetY = 6;
                            ctx.lineWidth = dataset.borderWidth;

                            ctx.beginPath();
                            meta.data.forEach(function(point, index) {
                                if (index === 0) {
                                    ctx.moveTo(point.x, point.y);
                                } else {
                                    ctx.lineTo(point.x, point.y);
                                }
                            });
                            ctx.strokeStyle = 'transparent';
                            ctx.stroke();

                            ctx.restore();
                        });
                    }
                }]
            });
        };

        var marketCapData = [0, 1, 3, 2, 5, 7, 5];
        var totalVolumeData = [0, 3, 1, 3, 1, 5, 2];
        var totalSupplyData = [2, 4, 2, 3, 2, 2, 1];

        // Creating other charts with shadow effect and adjusted x-axis labels
        createChartWithShadow('market-cap-chart', marketCapData, 'rgba(54, 162, 235, 1)');
        createChartWithShadow('total-volume-chart', totalVolumeData, 'rgba(75, 192, 192, 1)');
        createChartWithShadow('total-supply-chart', totalSupplyData, 'rgba(153, 102, 255, 1)');
        
        // Additional Charts
        var salesThisMonthData = [3, 2, 1, 6, 5, 8];
        var salesLastMonthData = [2, 2, 1, 5, 4, 7];
        var rentalThisMonthData = [3, 2, 1, 6, 5, 8];
        var rentalLastMonthData = [2, 1, 4, 5, 4, 7];
        var newSignUpsData = [1, 1, 5, 6, 4, 8];
        var activeUsersData = [1, 2, 4, 1, 5, 6];
        var monthlyExpensesData = [1, 1, 5, 6, 4, 8];
        var annualExpensesData = [1, 3, 4, 1, 2, 7];
        var productSalesData = [1, 2, 5, 2, 4, 8];
        var serviceIncomeData = [1, 2, 1, 5, 3, 7];
        var topRegionData = [1, 1, 5, 3, 7, 8];
        var topCityData = [1, 1, 4, 5, 3, 7];

        createChartWithShadow('sales-this-month-chart', salesThisMonthData, 'rgba(54, 162, 235, 1)');
        createChartWithShadow('sales-last-month-chart', salesLastMonthData, 'rgba(75, 192, 192, 1)');
        createChartWithShadow('rental-this-month-chart', rentalThisMonthData, 'rgba(54, 162, 235, 1)');
        createChartWithShadow('rental-last-month-chart', rentalLastMonthData, 'rgba(75, 192, 192, 1)');
        createChartWithShadow('new-sign-ups-chart', newSignUpsData, 'rgba(54, 162, 235, 1)');
        createChartWithShadow('active-users-chart', activeUsersData, 'rgba(75, 192, 192, 1)');
        createChartWithShadow('monthly-expenses-chart', monthlyExpensesData, 'rgba(54, 162, 235, 1)');
        createChartWithShadow('annual-expenses-chart', annualExpensesData, 'rgba(75, 192, 192, 1)');
        createChartWithShadow('product-sales-chart', productSalesData, 'rgba(54, 162, 235, 1)');
        createChartWithShadow('service-income-chart', serviceIncomeData, 'rgba(75, 192, 192, 1)');
        createChartWithShadow('top-region-chart', topRegionData, 'rgba(54, 162, 235, 1)');
        createChartWithShadow('top-city-chart', topCityData, 'rgba(75, 192, 192, 1)');
    }

    // Call the functions to create the charts
    createRevenueChart();
    createOtherCharts();
</script>








</body>
</html>