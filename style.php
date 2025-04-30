<?php header("Content-type: text/css"); ?>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #fff;
    color: #222;
}

header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    z-index: 1000;
    background: #007BFF; /* bleu */
    color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.07);
    min-height: 56px;
}

/* Pour que le texte du header soit blanc */
header, header a, header span {
    color: #fff !important;
    font-family: Arial, sans-serif;
    font-size: 1.08em;
    font-weight: 500;
    letter-spacing: 0.01em;
    text-decoration: none;
}

a {
    font-family: Arial, sans-serif;
    font-size: 1.08em;
    color: #007BFF;
    font-weight: 500;
    letter-spacing: 0.01em;
    text-decoration: none;
    transition: color 0.2s, text-decoration 0.2s;
}
a:hover, a:focus {
    color: #1DA1F2;
    text-decoration: underline;
}

.sidebar nav ul {
    list-style: none;
    padding: 0;
    margin: 0 0 10px 0;
}

.sidebar nav ul li a {
    color: white;
    text-decoration: none;
    transition: 0.3s;
    font-weight: bold;
    font-size: 1.08em;
    font-family: Arial, sans-serif;
    letter-spacing: 0.01em;
    /* Ajouté pour uniformiser */
    text-shadow: 0 1px 2px rgba(0,0,0,0.10);
}
.sidebar nav ul li a:hover,
.sidebar nav ul li a:active {
    text-decoration: underline;
    color: #1DA1F2;
}

.sidebar-social-link {
    color: white;
    text-decoration: none;
    transition: 0.3s;
    font-weight: bold;
    font-size: 1.08em;
    font-family: Arial, sans-serif;
    letter-spacing: 0.01em;
}
.sidebar-social-link:hover {
    color: #1DA1F2;
    text-decoration: underline;
}

footer.social {
    text-align: center;
    margin-top: 20px;
}

footer.social a {
    display: block;
    margin: 5px;
    color: #1DA1F2;
    text-decoration: none;
    font-family: Arial, sans-serif;
    font-size: 1.08em;
    font-weight: 500;
    letter-spacing: 0.01em;
    text-decoration: none;
    transition: color 0.2s, text-decoration 0.2s;
}
footer.social a:first-child {
    color: #0077b5;
}
footer.social a:hover {
    text-decoration: underline;
    color: #1DA1F2;
}

/* Sidebar occupe toute la hauteur et commence tout en haut */
.sidebar {
    width: 220px;
    background-color: #333;
    color: white;
    padding: 20px;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 1100;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    /* Ajouté pour superposer la sidebar */
    box-shadow: 2px 0 12px rgba(0,0,0,0.10);
    /* transform et transition sont gérés inline pour compatibilité JS */
}

/* Pour masquer la sidebar sur mobile, on pourrait ajouter un media query si besoin */

/* Le bouton d'ouverture est déjà stylé inline */

/* Adapter le container pour ne plus décaler le contenu à gauche */
.container {
    display: flex;
    min-height: 100vh;
    padding-top: 56px; /* pour le header */
    margin-left: 0; /* plus de décalage par défaut */
}

.content {
    flex: 1;
    padding: 20px;
    font-family: Arial, sans-serif;
    font-size: 1.13em;
    color: #222;
    line-height: 1.7;
}

.main-article {
    background-color: white;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
    font-size: 1.18em;
    color: #222;
    line-height: 1.7;
}

.other-articles {
    display: block;
    max-width: 800px;
    margin: 0 auto 20px auto; /* centre la section et limite la largeur */
    width: 100%;
    /* gap: 20px;  <-- à retirer si présent */
    font-family: Arial, sans-serif;
}

.other-articles article {
    background-color: white;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.13);
    border-radius: 10px;
    max-width: 100%;
    width: 100%;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 1.13em;
    color: #222;
    line-height: 1.7;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
article:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

h1, h2, h3, h4, h5, h6 {
    font-family: Arial, sans-serif;
    color: #1a1a1a;
    font-weight: bold;
    letter-spacing: 0.01em;
}

textarea, input, select, button {
    font-family: Arial, sans-serif;
    font-size: 1em;
}

@media (max-width: 900px) {
    .container {
        flex-direction: column;
        padding-top: 56px;
        margin-left: 0;
    }
    .sidebar {
        width: 100vw !important;
        left: 0;
        height: 100vh;
        min-width: unset;
        max-width: unset;
        box-shadow: 0 2px 12px rgba(0,0,0,0.13);
        padding: 16px 8px 60px 8px;
        font-size: 1em;
    }
    #sidebar {
        transform: translateX(-100vw);
    }
    .content {
        padding: 10px;
        font-size: 1em;
    }
    .main-article, .other-articles article {
        padding: 12px;
        font-size: 1em;
    }
    .other-articles {
        max-width: 98vw;
        padding: 0 2vw;
    }
    header {
        min-height: 48px;
        font-size: 1em;
        padding: 6px 8px;
    }
}

@media (max-width: 600px) {
    .container {
        padding-top: 44px;
    }
    .sidebar {
        width: 100vw !important;
        padding: 10px 2vw 60px 2vw;
        font-size: 0.98em;
    }
    .content {
        padding: 4vw 2vw;
        font-size: 0.98em;
    }
    .main-article, .other-articles article {
        padding: 8px;
        font-size: 0.97em;
    }
    .other-articles {
        max-width: 100vw;
        padding: 0 1vw;
    }
    img, .main-article img, .other-articles img {
        max-width: 100vw !important;
        height: auto !important;
    }
    h1, h2, h3, h4, h5, h6 {
        font-size: 1.1em !important;
    }
    header {
        min-height: 38px;
        font-size: 0.97em;
        padding: 4px 4px;
    }
}

/* Pour les articles en flex sur la page d'accueil */
@media (max-width: 1200px) {
    .home-flex-articles {
        flex-direction: column !important;
        gap: 30px !important;
        width: 100vw !important;
        margin-left: 0 !important;
    }
    .home-main-article {
        margin-left: 0 !important;
        max-width: 98vw !important;
    }
    .home-side-articles {
        margin-left: 0 !important;
        margin-top: 0 !important;
        max-width: 98vw !important;
    }
}

/* Pour éviter le débordement horizontal */
html, body {
    max-width: 100vw;
    overflow-x: hidden;
}

/* Mode sombre */
body.dark-mode {
    background: #181a1b !important;
    color: #e0e0e0 !important;
}
body.dark-mode header,
body.dark-mode header a,
body.dark-mode header span {
    background: #23272b !important;
    color: #e0e0e0 !important;
}
body.dark-mode .sidebar {
    background: #23272b !important;
    color: #e0e0e0 !important;
}
body.dark-mode .sidebar nav ul li a,
body.dark-mode .sidebar-social-link {
    color: #e0e0e0 !important;
}
body.dark-mode .content,
body.dark-mode .main-article,
body.dark-mode .other-articles article,
body.dark-mode .other-articles,
body.dark-mode .container {
    background: #23272b !important;
    color: #e0e0e0 !important;
}
body.dark-mode input,
body.dark-mode textarea,
body.dark-mode select,
body.dark-mode button {
    background: #23272b !important;
    color: #e0e0e0 !important;
    border-color: #444 !important;
}
body.dark-mode a {
    color: #7abaff !important;
}
body.dark-mode a:hover,
body.dark-mode a:focus {
    color: #1DA1F2 !important;
}
body.dark-mode footer.social a {
    color: #7abaff !important;
}
body.dark-mode .sidebar-social-link:hover {
    color: #1DA1F2 !important;
}
body.dark-mode .main-article,
body.dark-mode .other-articles article {
    background: #23272b !important;
    color: #e0e0e0 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
body.dark-mode .sidebar {
    box-shadow: 2px 0 12px rgba(0,0,0,0.25);
}
body.dark-mode img {
    filter: brightness(0.93) contrast(1.08);
}