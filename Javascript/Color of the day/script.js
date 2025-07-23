const $form = document.getElementById('form')
const $date = document.getElementById('date')
const $history = document.getElementById('history')
const $clear = document.getElementById('clear')
let searchHistory = JSON.parse(localStorage.getItem('searchedColor')) || []

function displayHistory(history) {
    $history.innerHTML = history.reduce((html, log, index) => html + `
    <div class="row">    
    <div data-index="${index}">
            <div style="background-color: ${log.hex}; width: 60%; height: 70%;">${log.date}</div>
            <div class="col-md-12 d-flex justify-content-end align-items-center">
          
            <button type="button" class="delete btn btn-close" aria-label="Delete"  data-index="${index}"></button></div>
        </div>
        <div>
    `, '')
}

$form.addEventListener('submit', async function(e) {
    e.preventDefault()
    const response = await fetch(`https://colors.zoodinkers.com/api?date= ${$date.value}`)
    const json = await response.json()
    searchHistory.unshift(json)
    localStorage.setItem("searchedColor", JSON.stringify(searchHistory))
    $form.reset()
    displayHistory(searchHistory)
})

$history.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete')) {
        const index = e.target.dataset.index
        searchHistory.splice(index, 1)
        localStorage.setItem('searchedColor', JSON.stringify(searchHistory))
        displayHistory(searchHistory)
    }
})

$clear.addEventListener('click', function(e) {
  searchHistory.splice(0, searchHistory.length)
  localStorage.setItem('searchedColor', JSON.stringify(searchHistory))
  displayHistory(searchHistory)
})

displayHistory(searchHistory)
