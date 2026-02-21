export function carregarXMLheader(context, file) {
    const options = {
        method: 'GET',
    }

    fetch(file, options)
        .then(response => {
            if (response.ok) {
                return response.text();
            } else {
                throw new Error(response.status());
            }
        })

    .then(function(data) {

        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(data, "text/xml");
        const dadesHeaderXML = xmlDoc.getElementsByTagName("header");


        let dadesHeader_v3 = {};
        dadesHeader_v3 = {
            web: dadesHeaderXML[0].getElementsByTagName("web")[0].textContent,
            logo: dadesHeaderXML[0].getElementsByTagName("logo")[0].textContent
        }
        console.log("ljbnf<a= " + dadesHeader_v3.logo);
        context.commit("mutationCrearDadesHeader_v3", dadesHeader_v3);
    })
}