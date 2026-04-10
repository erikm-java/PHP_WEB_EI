<?php
$pageTitle = 'Política de Cookies i Protecció de Dades';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/PHP_WEB_EI/index.php">Inici</a></li>
            <li class="breadcrumb-item active">Política de Cookies i Protecció de Dades</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Índex flotant -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top:80px;">
                <div class="card-header bg-success text-white fw-bold small">Contingut</div>
                <div class="list-group list-group-flush small rounded-bottom-3">
                    <a class="list-group-item list-group-item-action py-2" href="#cookies">Política de Cookies</a>
                    <a class="list-group-item list-group-item-action py-2" href="#tipus">Tipus de cookies</a>
                    <a class="list-group-item list-group-item-action py-2" href="#gestio">Gestió de cookies</a>
                    <a class="list-group-item list-group-item-action py-2" href="#proteccio-dades">Protecció de Dades</a>
                    <a class="list-group-item list-group-item-action py-2" href="#drets">Els teus drets</a>
                    <a class="list-group-item list-group-item-action py-2" href="#contacte-pd">Contacte</a>
                </div>
            </div>
        </div>

        <!-- Contingut legal -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-3 p-4 p-lg-5">

                <h1 class="fw-bold text-success mb-2">Política de Cookies i Protecció de Dades</h1>
                <p class="text-muted small mb-5">Darrera actualització: 10 d'abril de 2026</p>

                <!-- COOKIES -->
                <section id="cookies" class="mb-5">
                    <h2 class="fw-bold border-bottom border-success pb-2 mb-4">
                        <i class="bi bi-cookie text-warning me-2"></i>Política de Cookies
                    </h2>
                    <p>En compliment de l'article 22.2 de la Llei 34/2002, d'11 de juliol, de Serveis de la Societat de la Informació i del Comerç Electrònic (LSSICE), el Reglament General de Protecció de Dades (RGPD) i la normativa de la Unió Europea, <strong>Jardins de Lliçà</strong> informa sobre l'ús de cookies al seu lloc web.</p>
                    <p>Una <strong>cookie</strong> és un fitxer de text que s'emmagatzema al teu dispositiu (ordinador, telèfon, tauleta) quan visites un lloc web. Les cookies permeten al lloc web recordar les teves preferències i millorar la teva experiència de navegació.</p>
                </section>

                <section id="tipus" class="mb-5">
                    <h3 class="fw-bold mb-4">Tipus de cookies que utilizem</h3>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-success">
                                <tr>
                                    <th>Nom de la cookie</th>
                                    <th>Tipus</th>
                                    <th>Durada</th>
                                    <th>Finalitat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>PHPSESSID</code></td>
                                    <td><span class="badge bg-primary">Tècnica / Sessió</span></td>
                                    <td>Sessió</td>
                                    <td>Gestió de la sessió d'usuari (login, dades personals temporals). Estrictament necessària.</td>
                                </tr>
                                <tr>
                                    <td><code>cart</code></td>
                                    <td><span class="badge bg-primary">Tècnica / Funcional</span></td>
                                    <td>7 dies</td>
                                    <td>Emmagatzema els productes de la cistella de la compra entre sessions. Estrictament necessària per al funcionament de la botiga.</td>
                                </tr>
                                <tr>
                                    <td><code>cookies_accepted</code></td>
                                    <td><span class="badge bg-secondary">Preferència</span></td>
                                    <td>365 dies</td>
                                    <td>Recorda si has acceptat la política de cookies per a no mostrar la notificació repetidament.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>No fem servir cookies de tercers, de seguiment ni de publicitat.</strong>
                        El mapa de contacte utilitza Google Maps, que pot establir les seves pròpies cookies. Consulta la <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">política de privacitat de Google</a>.
                    </div>
                </section>

                <section id="gestio" class="mb-5">
                    <h3 class="fw-bold mb-4">Com gestionar les cookies</h3>
                    <p>Pots configurar el teu navegador per a rebutjar les cookies o eliminar les existents. Tingues en compte que desactivar les cookies tècniques pot afectar el funcionament de la cistella de la compra i el login.</p>
                    <div class="row g-3">
                        <?php foreach ([
                            ['Chrome', 'bi-google', 'Configuració > Privacitat i seguretat > Cookies i altres dades dels llocs'],
                            ['Firefox', 'bi-browser-firefox', 'Preferències > Privacitat i seguretat > Cookies i dades dels llocs'],
                            ['Safari', 'bi-apple', 'Preferències > Privacitat > Gestionar dades de llocs web'],
                            ['Edge', 'bi-browser-edge', 'Configuració > Privacitat, cerca i serveis > Cookies'],
                        ] as $browser): ?>
                        <div class="col-sm-6">
                            <div class="border rounded-3 p-3">
                                <h6 class="fw-bold"><i class="bi <?= $browser[1] ?> me-2 text-success"></i><?= $browser[0] ?></h6>
                                <p class="small text-muted mb-0"><?= $browser[2] ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <hr class="my-5">

                <!-- PROTECCIÓ DE DADES -->
                <section id="proteccio-dades" class="mb-5">
                    <h2 class="fw-bold border-bottom border-success pb-2 mb-4">
                        <i class="bi bi-shield-lock-fill text-success me-2"></i>Protecció de Dades Personals
                    </h2>

                    <h4 class="fw-bold">Responsable del tractament</h4>
                    <div class="bg-light rounded-3 p-4 mb-4">
                        <ul class="list-unstyled mb-0">
                            <li><strong>Entitat:</strong> Jardins de Lliçà</li>
                            <li><strong>Adreça:</strong> Carrer Major, 15, 08186 Lliçà d'Amunt (Vallès Oriental)</li>
                            <li><strong>Correu:</strong> <a href="mailto:info@jardinsllica.cat">info@jardinsllica.cat</a></li>
                            <li><strong>Telèfon:</strong> 938 428 000</li>
                        </ul>
                    </div>

                    <h4 class="fw-bold">Finalitat i base legal del tractament</h4>
                    <p>Les dades personals que recopilem s'utilitzen per a:</p>
                    <ul>
                        <li>Gestionar el teu compte d'usuari i permetre't iniciar sessió.</li>
                        <li>Processar i gestionar les teves comandes.</li>
                        <li>Enviar comunicacions relacionades amb la teva comanda o el teu compte.</li>
                        <li>Complir amb les obligacions legals (fiscals, comptables).</li>
                    </ul>
                    <p>La base legal és el consentiment de l'usuari i l'execució del contracte de compravenda.</p>

                    <h4 class="fw-bold">Conservació de les dades</h4>
                    <p>Les teves dades es conservaran mentre mantinguis el teu compte actiu. En cas de sol·licitar la baixa, les dades s'eliminaran en el termini màxim de 30 dies, excepte les que cal conservar per obligació legal.</p>

                    <h4 class="fw-bold">Destinataris</h4>
                    <p>No cedim les teves dades a tercers, excepte quan sigui estrictament necessari per a l'execució de la comanda (empresa de transport) o per obligació legal.</p>
                </section>

                <section id="drets" class="mb-5">
                    <h3 class="fw-bold mb-4">Els teus drets (RGPD)</h3>
                    <div class="row g-3">
                        <?php foreach ([
                            ['bi-eye-fill', 'Accés', 'Tens dret a saber quines dades tenim sobre tu.'],
                            ['bi-pencil-fill', 'Rectificació', 'Pots modificar les teves dades si no són correctes.'],
                            ['bi-trash-fill', 'Supressió', 'Pots demanar l\'eliminació de les teves dades ("dret a l\'oblit").'],
                            ['bi-slash-circle-fill', 'Oposició', 'Pots oposar-te al tractament de les teves dades.'],
                            ['bi-download', 'Portabilitat', 'Pots rebre les teves dades en un format estructurat.'],
                            ['bi-pause-circle-fill', 'Limitació', 'Pots sol·licitar la limitació del tractament de les teves dades.'],
                        ] as $right): ?>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-center p-3 border rounded-3 h-100">
                                <i class="bi <?= $right[0] ?> fs-3 text-success mb-2"></i>
                                <h6 class="fw-bold"><?= $right[1] ?></h6>
                                <p class="small text-muted mb-0"><?= $right[2] ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section id="contacte-pd">
                    <h3 class="fw-bold mb-3">Exercir els teus drets / Contacte DPD</h3>
                    <p>Per a exercir qualsevol dels teus drets o per a qualsevol consulta sobre privacitat, posa't en contacte amb nosaltres:</p>
                    <div class="bg-light rounded-3 p-4">
                        <p class="mb-1"><i class="bi bi-envelope-fill text-success me-2"></i><a href="mailto:privacitat@jardinsllica.cat">privacitat@jardinsllica.cat</a></p>
                        <p class="mb-0"><i class="bi bi-info-circle-fill text-success me-2"></i>També pots presentar una reclamació davant l'<strong>Agència Espanyola de Protecció de Dades (AEPD)</strong> a <a href="https://www.aepd.es" target="_blank" rel="noopener">www.aepd.es</a>.</p>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
