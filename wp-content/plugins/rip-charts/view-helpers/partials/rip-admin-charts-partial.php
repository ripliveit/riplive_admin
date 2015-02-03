<div id="admin-charts" ng-app="adminChart">
    <h1>
        Gestione Classifiche
    </h1>

    <section id="charts">
        <div ng-view></div>
    </section>

    <alert>
        <div id="overlay">
            <div id="alert" class="alert">
                <div id="alert-type"></div>
                
                <h3 id="alert-message">
                    Questo è il messaggio che verrà visualizzato.
                </h3>

                <div class="btn warning" ng-click="closeAlert()">
                    Chiudi
                </div>
            </div>
        </div>
    </alert>
</div>

<script src="/wp-content/plugins/rip-charts/assets/js/app.js"></script>
<script src="/wp-content/plugins/rip-charts/assets/js/services/chart-service.js"></script>
<script src="/wp-content/plugins/rip-charts/assets/js/controllers/list-controller.js"></script>
<script src="/wp-content/plugins/rip-charts/assets/js/controllers/create-controller.js"></script>
<script src="/wp-content/plugins/rip-charts/assets/js/controllers/update-controller.js"></script>
<script src="/wp-content/plugins/rip-charts/assets/js/controllers/pagination-controller.js"></script>
<script src="/wp-content/plugins/rip-charts/assets/js/directives/alert.js"></script>