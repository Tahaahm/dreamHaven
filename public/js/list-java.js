$(document).ready(function () {
  let container = $('.container'); // Use .date as the itemSelector

  function initializeIsotope () {
    container.isotope({
      itemSelector: '.item-container',
      getSortData: {
        date: function (item) {
          const dateStr = $(item).find('.item-date').text()
          const dateParts = dateStr.split('/')
          const year = parseInt(dateParts[2])
          const month = parseInt(dateParts[1]) - 1
          const day = parseInt(dateParts[0])
          return new Date(year, month, day)
        },
        number: function (item) {
          const priceStr = $(item).find('.item-price').text()
          // Remove the "$" symbol and parse as a number
          const price = parseFloat(priceStr.replace('$', ''))
          return price
        }
      }
    })

    // Initially, set the default sorting to date
    container.isotope({ sortBy: 'date', sortAscending: false })
  }

  function sortBYdate () {
    // Sort by date descending
    container.isotope({ sortBy: 'date', sortAscending: false })
  }

  function sortBYprice () {
    // Sort by price ascending
    container.isotope({ sortBy: 'number', sortAscending: true })
  }

  // Call the initialization function
  initializeIsotope()

  // Use jQuery to attach click event listeners to the buttons
  $('#sort-by-date-button').click(sortBYdate)
  $('#sort-by-price-button').click(sortBYprice)

  // Trigger Isotope layout once the page is fully loaded
  $(window).on('load', function () {
    container.isotope('layout')
  })
})

let DateBTN = document.getElementById('sort-by-date-button')
let PriceBTN = document.getElementById('sort-by-price-button')

PriceBTN.addEventListener('click' , function () {
  DateBTN.style.backgroundColor = '#ebebeb'
  DateBTN.style.color = '#666666'

  PriceBTN.style.backgroundColor = '#2dc997'
  PriceBTN.style.color = 'white'
})

DateBTN.addEventListener('click' , function () {
  DateBTN.style.backgroundColor = '#2dc997'
  DateBTN.style.color = 'white'

  PriceBTN.style.backgroundColor = '#ebebeb'
  PriceBTN.style.color = '#666666'
})
