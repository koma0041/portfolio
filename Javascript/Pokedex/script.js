const $container = document.getElementById('container');
const $pokeList = document.getElementById('pokemens');
const $more = document.getElementById('more');
const $dialog = document.getElementById('dialog');

async function pokedata(api) {
    const response = await fetch(api);
    const data = await response.json();
    return data;
}

function parseUrl(url) {
    return url.substring(url.substring(0, url.length - 2).lastIndexOf('/') + 1, url.length - 1);
}

function showopokemen() {
    pokedata("https://pokeapi.co/api/v2/pokemon?limit=30&offset=0").then(data => {
        const loadMore = data['loadMore'];
        const display = data['results'];
        $more.value = loadMore;
        appendHtml(display);
    });
}

$more.addEventListener('click', async function (e) {
        const loadMoreData = await pokedata(e.target.value);
        const loadMoreResults = loadMoreData['results'];
        $more.value = loadMoreData['loadMore'];
        appendHtml(loadMoreResults);
    
});

function appendHtml(results) {
    let className = "";
    const ls = JSON.parse(localStorage.getItem('caught-pokes')) || [];
    results.forEach(h => {
        if (ls.includes(parseUrl(h.url))) {
            className = "poke-catch";
        } else {
            className = "poke-release";
        }
        $pokeList.insertAdjacentHTML('beforeend', `
                <div class=" ${className}" id="img-id-${parseUrl(h.url)}">
                    <div class="data-id=${parseUrl(h.url)}">
                        <picture class="picture" name="${h.name}" url="${h.url}" data-hdimage="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/${parseUrl(h.url)}.png">
                            <source srcset="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/${parseUrl(h.url)}.png 475w">
                            <img class="poke-img" src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/${parseUrl(h.url)}.png">
                        </picture>
                    </div>
                    <div>${h.name}</div>
                </div>
         `);
    });
}


let CatchAction = "";

$container.addEventListener('click', async function (e) {
    const $dialogImage = e.target.closest('picture');
    const ls = JSON.parse(localStorage.getItem('catch History'));
    const history = ls ? ls : [];
    if ($dialogImage) {
        const name = $dialogImage.getAttribute('name');
        const url = $dialogImage.getAttribute('url');
        pokeId = parseUrl(url);
        let buttonStyle = "";
        let className = "";
        if (history.includes(pokeId)) {
            CatchAction = "release";
            buttonText = "Release";
            className = "poke-catch";
        } else {
            CatchAction = "catch";
            buttonText = "Catch";
            className = "poke-release";
        }
        
        data = await pokedata(url);
        types = data["types"].map(t => t.type.name).join();
        moves = data["moves"].map(m => m.move.name).slice(0, 6).join();
        
        if (CatchAction == "catch") {
            const $catch = document.getElementById('catch');
            $catch.addEventListener('click', function (e) {
                history.push(pokeId);
                localStorage.setItem('caught-pokes', JSON.stringify(history));
                $dialog.close();
                const $poke = document.getElementById(`img-id-${pokeId}`);
                $poke.classList.remove('poke-release');
                $poke.classList.add('poke-catch');
            });
        } else if (CatchAction == "release") {
            const $release = document.getElementById('release');
            $release.addEventListener('click', function (e) {
                const index = history.indexOf(pokeId);
                history.splice(index, 1);
                localStorage.setItem('caught-pokes', JSON.stringify(history));
                $dialog.close();
                const $poke = document.getElementById(`img-id-${pokeId}`);
                $poke.classList.remove('poke-catch');
                $poke.classList.add('poke-release');
            });
        }
    }
});



showopokemen();

