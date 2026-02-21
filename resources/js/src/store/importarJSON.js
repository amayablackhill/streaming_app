export function carregarJSONfooter(context, file) {
    const options = {
        method: 'GET',
    }
    fetch(file, options)
    
    .then(response => {
        if (response.ok) 
            return response.json();
        }).
        
    then(function(data) {
        console.log("list " + data.footer.web);
            context.commit("mutationCarregarJSON", data);
        })
} 

export function carregarJSONMovies(context, file) {
    const options = {
        method: 'GET',
    }
    fetch(file, options)
    
    .then(response => {
        if (response.ok) 
            return response.json();
        }).
        
    then(function(data) {
        data.peliculas.forEach(movie => {
            context.commit("mutationCarregarJSONMovies", movie);  
        });
    })
}