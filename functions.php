<?php 

// Ajouter la prise en charge des images mises en avant
add_theme_support( 'post-thumbnails' );

// Ajouter automatiquement le titre du site dans l'en-tête du site
add_theme_support( 'title-tag' );


function check_for_theme_update($transient) {
    var_dump("coucou");
    if (empty($transient->checked)) {
        return $transient;
    }

    // Définir les informations du dépôt GitHub
    $theme_slug = 'wordpress_theme'; // Le slug de votre thème
    $github_api_url = 'https://api.github.com/repos/Loic-MEHAL/'.$theme_slug.'/releases/latest';
    $theme_version = wp_get_theme()->get('Version');  // Version actuelle du thème

    // Faire une requête à l'API GitHub pour obtenir les informations sur la dernière release
    $response = wp_remote_get($github_api_url);

    if (!is_wp_error($response) && isset($response['body'])) {
        // Décoder la réponse JSON
        $release = json_decode($response['body']);
        
        // Si la version taguée sur GitHub est plus récente, proposer une mise à jour
        if (isset($release->tag_name) && version_compare($theme_version, $release->tag_name, '<')) {
            $transient->response[get_template()] = array(
                'theme'       => get_template(),              // Slug du thème
                'new_version' => $release->tag_name,          // Version GitHub
                'url'         => $release->html_url,          // URL vers la release GitHub
                'package'     => $release->zipball_url,       // URL pour télécharger le zip de la release
            );
        }
    }

    return $transient;
}

add_filter('pre_set_site_transient_update_themes', 'check_for_theme_update');


// Fonction pour ajouter des détails supplémentaires à la mise à jour du thème
function add_theme_update_details($response, $action, $args) {
    if ($action !== 'theme_information') {
        return $response;
    }

    // Vérifier que c'est bien notre thème
    $theme_slug = 'wordpress_theme';
    if ($args->slug !== $theme_slug) {
        return $response;
    }

    // Obtenir les informations sur la dernière release via l'API GitHub
    $github_api_url = 'https://api.github.com/repos/Loic-MEHAL/'.$theme_slug.'/releases/latest';
    $request = wp_remote_get($github_api_url);

    if (is_wp_error($request)) {
        return $response;
    }

    $release = json_decode(wp_remote_retrieve_body($request));

    // Ajouter les informations pertinentes
    if (isset($release->tag_name)) {
        $response->new_version = $release->tag_name;
        $response->url = $release->html_url;
        $response->package = $release->zipball_url;
    }

    return $response;
}

// Ajouter le filtre pour les informations sur le thème
add_filter('themes_api', 'add_theme_update_details', 10, 3);