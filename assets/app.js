import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.scss';

// const $ = require('jquery');

// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap');

// loads the jquery package from node_modules
import $ from 'jquery';

$(document).ready(function () {

    $("#filter").change(function () {

        // Fonction pour effectuer la requête asynchrone
        async function fetchData(filter) {
            try {
                // Construit l'URL avec le filtre
                const url = `/filter/${filter}`;

                // Exécute la requête asynchrone
                const response = await fetch(url, {
                    method: 'GET', // Méthode HTTP
                    headers: {
                        'Content-Type': 'application/json', // Type de contenu attendu de la réponse
                    },
                });

                // Vérifie si la requête a réussi
                if (!response.ok) {
                    throw new Error(`Erreur: ${response.status}`); // Lance une exception si la réponse est une erreur
                }

                // Extrait les données JSON de la réponse
                const data = await response.json();

                let listArticles = "";

                for(let i = 0; i < data.length; i++) {

                    listArticles += "<a href='{{path('app_article_show', { id : " + data[i].id + " })}}'>" +
						"<div class='d-flex article p-3'>"+

                            // "<img class='col-md-4' src='{{ asset('/uploads/articles/default.jpg') }}' alt='" + data[i].title  + "' title='" + data[i].title  + "'>"+
                            "<img class='col-md-4' src='/uploads/articles/" + data[i].picture  + "' alt='" + data[i].title  + "' title='" + data[i].title  + "'>"+

							"<div class='col-md-8 d-flex flex-column ms-3'>"+
								"<h3>"+
									data[i].title +
								"</h3>"+
								"<p>"+
                                data[i].description +
								"</p>"+
							"</div>"+
						"</div>"+
					"</a>";

                }

                $("#list-articles").html(
                    listArticles
                );



            } catch (error) {
                console.error("Il y a eu une erreur avec la requête fetch: ", error.message);
            }
        }

        let filter = $(this).val();
        console.log(filter);
        // Appel de la fonction avec le filtre désiré, par exemple 'monFiltre'
        fetchData(filter);

    });

});

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

