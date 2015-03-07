<div id="admin-podcasts" ng-app="adminPodcasts">
    <h1>
        Gestione Podcasts
    </h1>

    <section id="podcasts">
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


<script src="/wp-content/plugins/rip-podcasts/assets/js/app.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/services/aws.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/services/s3.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/services/id3.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/services/data-service.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/services/podcasts-service.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/filters/size-filter.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/controllers/programs-controller.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/controllers/podcasts-controller.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/controllers/update-controller.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/controllers/upload-controller.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/controllers/pagination-controller.js"></script>
<script src="/wp-content/plugins/rip-podcasts/assets/js/directives/alert.js"></script>