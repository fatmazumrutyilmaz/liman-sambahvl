@component('modal-component',[
        "id" => "infoModal",
        "title" => "Sonuç Bilgisi",
        "footer" => [
            "text" => "OK",
            "class" => "btn-success",
            "onclick" => "hideInfoModal()"
        ]
    ])
@endcomponent

@component('modal-component',[
        "id" => "changeModal",
        "title" => "Rol Seçimi",
        "footer" => [
            "text" => "AL",
            "class" => "btn-success",
            "onclick" => "hideChangeModal()"
        ]
    ])
    @include('inputs', [
        "inputs" => [
            "Roller:newType" => [
                "SchemaMasterRole" => "schema",
                "InfrastructureMasterRole" => "infrastructure",
                "RidAllocationMasterRole" => "rid",
                "PdcEmulationMasterRole" => "pdc",
                "DomainNamingMasterRole" => "naming",
                "DomainDnsZonesMasterRole" => "domaindns",
                "ForestDnsZonesMasterRole" => "forestdns",
                "All" => "all"
            ],
        ]
    ])
@endcomponent

@component('modal-component',[
        "id" => "createTrustRelationModal",
        "title" => "Create Trust Relation",
        "footer" => [
            "text" => "Create",
            "class" => "btn-success",
            "onclick" => "createTrustRelation()"
        ]
    ])
    @include('inputs', [
        "inputs" => [
            "Domain Name" => "newDomainName:text:deneme.lab",
            "IP Address" => "newIpAddr:text",
            "Type:newType" => [
                "forest" => "forest",
                "external" => "external"
            ],
            "Direction:newDirection" => [
                "incoming" => "incoming",
                "outgoing" => "outgoing",
                "both" => "both"
            ],
            "Create Location:newCreateLocation" => [
                "local" => "local",
                "both" => "both"
            ],
            "Username" => "newUsername:text",
            "Password" => "password:password"
        ]
    ])
@endcomponent

@component('modal-component',[
        "id" => "trustedServerDetailsModal",
        "title" => "Details",
        "footer" => [
            "text" => "Close",
            "class" => "btn-success",
            "onclick" => "closeTrustedServerDetailsModal()"
        ]
    ])
@endcomponent

@component('modal-component',[
        "id" => "deleteTrustedServerModal",
        "title" => "Warning",
        "footer" => [
            "text" => "Cancel",
            "class" => "btn-success",
            "onclick" => "closeTrustedServerDetailsModal()"
        ],
        "footer" => [
            "text" => "Delete",
            "class" => "btn-danger",
            "onclick" => "destroyTrustRelation()"
        ]
    ])
        @include('inputs', [
        "inputs" => [
            "Password" => "password:password"
        ]
    ])
@endcomponent

<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active"  onclick="tab1()" href="#tab1" data-toggle="tab">Kurulum</a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link " onclick="tab5()" href="#tab5" data-toggle="tab">Etki Alanı Oluştur</a>
    </li>

    <li class="nav-item">
        <a class="nav-link "  onclick="tab2()" href="#tab2" data-toggle="tab">Samba Servis Durumu</a>
    </li>

    <li class="nav-item">
        <a class="nav-link "  onclick="printTable()" href="#fsmo" data-toggle="tab">FSMO Rol Yönetimi</a>
    </li>

    <li class="nav-item">
        <a class="nav-link "  onclick="trustedServers()" href="#trustRelation" data-toggle="tab">Trusted Servers</a>
    </li>
</ul>


<div class="tab-content">
    <div id="tab1" class="tab-pane active">
        <p>SambaHVL paketini kurmak için lütfen aşağıdaki butonu kullanın.</p>
        <button class="btn btn-success mb-2" id="1" onclick="installSmbPackage()">SambaHVL Paketini Kur</button>
        <div id="smbInstallStatus">  </div>
        <pre id="smbinstall">   </pre>
        <div id="smblast">  </div>
    </div>

    <div id="tab2" class="tab-pane">   
        <pre id="sambaLog">   
        
        </pre>
    </div>

    <div id="tab5" class="tab-pane">  
        <p>Etki alanı kurmak için lütfen aşağıdaki butonu kullanın.</p>
        <button class="btn btn-success mb-2" id="createDomainButton" onclick="createDomain()" type="button">Etki Alanı Oluştur</button>
        <div id="domainStatus"></div> 
        <pre id="domainLogs" class="tab-pane">    
        </pre>
    </div>

    <div id="fsmo" class="tab-pane">
        <p>Tablo üzerinde sağ tuş ile bir rolü üzerinize alabilir veya bunun için butonları kullanabilirsiniz.</p>
        <br />
        <button class="btn btn-success mb-2" id="btn1" onclick="showInfoModal()" type="button">Tüm rolleri al</button>
        <button class="btn btn-success mb-2" id="btn2" onclick="showChangeModal()" type="button">Belirli bir rolü al</button>
        <div class="table-responsive" id="fsmoTable"></div>
    </div>

    <div id="trustRelation" class="tab-pane">
        <button class="btn btn-success mb-2" id="createButton" onclick="showCreateTrustRelationModal()" type="button">Create</button>    
        <div id="trustedServers">
        </div>
    </div>

</div>

<script>
    var domainName = "";
    var passwd = "";

    if(location.hash === ""){
        tab1();
    }

    // Install SambaHvl Package == Tab 1 == 

    function tab1(){
        var form = new FormData();
        request(API('verifyInstallation'), form, function(response) {
            $('#smblast').html("");
            message = JSON.parse(response)["message"];
            let x = document.getElementById("1");
            if(message == true){
                x.disabled = true;
                $('#smbinstall').html("\nPaket zaten yüklü !");
            } else{
                x.disabled = false;
            }
        }, function(error) {
            showSwal(error.message, 'error', 3000);
            console.log(error);
        });
    }

    function installSmbPackage(){
        var form = new FormData();
        $('#smbInstallStatus').html("<b>SambaHvl kuruluyor. Lütfen kayıtları takip ediniz.</b>");
        request(API('installSmbPackage'), form, function(response) {
            observe();
        }, function(error) {
            $('#smbinstall').html("Hata oluştu");
        });
    }
    
    function observe(){
        var form = new FormData();
        request(API('observeInstallation'), form, function(response) {
            let json = JSON.parse(response);
            setTimeout(() => {
                observe();
            }, 1000);
          $("#smbinstall").text(json["message"]);
        }, function(response) {
            let error = JSON.parse(response);
            if(error["status"] == 202){
            $('#smblast').html(error);
           } else{
            showSwal(error, 'error', 3000);
           }

        });
    }

    // Create New Domain == Tab 2 ==

    function tab5(){
        var form = new FormData();
        request(API('verifyDomain'), form, function(response) {
            message = JSON.parse(response)["message"];
            let x = document.getElementById("createDomainButton");
            if(message == true){
                x.disabled = true;
                returnDomainInformations();
            } else{
                x.disabled = false;
            }
        }, function(error) {
            $('#tab1').html("Hata oluştu");
        });
    }

    
    function createDomain(){
        var form = new FormData();
        $('#domainStatus').html("<b>Etki alanı oluşturuluyor. Lütfen bekleyiniz.</b>");
        request(API('createSambaDomain'), form, function(response) {
            returnDomainInformations();
        }, function(error) {
            $('#smbinstall').html("Hata oluştu");
        });
    }
    

    function returnDomainInformations(){
        var form = new FormData();
        request(API('returnDomainInformations'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#domainStatus').html("<b>Etki alanı bilgileri :</b>");
            $('#domainLogs').html("\n" + message);
        }, function(error) {
            $('#tab2').html("Hata oluştu");
        });
    }

    // Control Samba4.service Status == Tab 3 ==

    function tab2(){
        var form = new FormData();
        request(API('tab2'), form, function(response) {
            message = JSON.parse(response)["message"];
            if(message == true){
                isActiveButton = '<button type="button" class="btn btn-success" disabled>Samba Servisi Aktif !</button>' ;
                $('#tab2').html(isActiveButton);

                var d1 = document.getElementById('tab2');
                d1.insertAdjacentHTML('beforeend', '<pre id="sambaLog">   </pre>');
                sambaLog();
            } else{
                isActiveButton = '<button type="button" class="btn btn-danger" disabled>Samba Servisi Aktif Değil !</button>' ;
                $('#tab2').html(isActiveButton);

            }
        }, function(error) {
            $('#tab3').html("Hata oluştu");
        });
    }

    function sambaLog(){
        var form = new FormData();
        request(API('sambaLog'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#sambaLog').html(message);
        }, function(error) {
            $('#sambaLog').html("Hata oluştu");
        });
    }

    // FSMO-Role Management  == Tab 4 ==

    function printTable(){
        showSwal('Yükleniyor...','info',2000);
        var form = new FormData();
        request(API('printTable'), form, function(response) {
            $('#fsmoTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
        
    }
    
    function takeTheRole(line){
        var form = new FormData();
        let contraction = line.querySelector("#contraction").innerHTML;
        form.append("contraction",contraction);

        request(API('takeTheRole'), form, function(response) {
            message = JSON.parse(response)["message"];
            if(message == ""){
                showSwal('Hata oluştu.', 'error', 7000);
            }
            else if(message.includes("successful")){
                printTable();
                showSwal(message,'success',7000);
            }
            else{
                showSwal(message,'info',7000);
            }
        }, function(error) {
            showSwal(error.message, 'error', 5000);

        });
    }
    
    function showInfoModal(line){
        showSwal('Yükleniyor...','info',3500);
        var form = new FormData();
        request(API('takeAllRoles'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#infoModal').find('.modal-body').html(
                "<pre>"+message+"</pre>"
            );
            $('#infoModal').modal("show");
        }, function(error) {
            showSwal(error.message, 'error', 5000);

        });
    }

    function hideInfoModal(line){
        $('#infoModal').modal("hide");
        printTable();
    }

    function showChangeModal(line){
        showSwal('Yükleniyor...','info',2000);
        $('#changeModal').modal("show");
    }

    function hideChangeModal(line){
        var form = new FormData();
        form.append("contraction", $('#changeModal').find('select[name=newType]').val());
        request(API('takeTheRole'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#changeModal').modal("hide");
            if(message == ""){
                showSwal('Hata oluştu.', 'error', 7000);
            }
            else if(message.includes("successful")){
                printTable();
                showSwal(message,'success',7000);
            }
            else{
                showSwal(message,'info',7000);
            }

        }, function(error) {
            $('#changeModal').modal("hide");
            showSwal(error.message, 'error', 5000);
        });
    }

    /**
     * Showing the servers which have trusted relation with this server
    */

    function trustedServers(){
        var form = new FormData();
        request("{{API('trustedServers')}}", form, function(response) {
            $('#trustedServers').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
          });;
        }, function(error) {
            $('#trustedServers').html("Hata oluştu");
        });
        
    }

    function showTrustedServerDetailsModal(line){
        var name = line.querySelector("#name").innerHTML;
        var type = line.querySelector("#type").innerHTML;
        var transitive = line.querySelector("#transitive").innerHTML;
        var direction = line.querySelector("#direction").innerHTML;
        console.log(name);
        if(name)
            $('#trustedServerDetailsModal h4.modal-title').html("Details");
        $('#trustedServerDetailsModal').find('.modal-body').html(
            "Name".bold() + "</br>" + name + "</br>" + "</br>" +
            "Type".bold() + "</br>" + type + "</br>" + "</br>" +
            "Transitive".bold() + "</br>" + transitive + "</br>" + "</br>" +
            "Direction".bold() + "</br>" + direction + "</br>" + "</br>"
        );
        $('#trustedServerDetailsModal').modal("show");
    }

    function closeTrustedServerDetailsModal(){
        $('#trustedServerDetailsModal').modal("hide");
    }

    /**
     * Deleting the servers which have trusted relation with this server
    */

    function closeDeleteTrustedServerModal(){
        $('#deleteTrustedServerModal').modal("hide");
    }

    function showDeleteTrustedServerModal(line){
        let name = line.querySelector("#name").innerHTML;
        domainName = name;
        $('#deleteTrustedServerModal').find('.modal-body').prepend(
            "If you destroy trust relation with \"".bold() + name.bold() + "\", please fill the password field.".bold() + "<br><br>");
        $('#deleteTrustedServerModal').find('.modal-footer')
            .append('<button type="button" class="btn btn-primary" onClick="closeDeleteTrustedServerModal()">Cancel</button>');
        $('#deleteTrustedServerModal').modal("show");
    }

    function destroyTrustRelation(){
        var form = new FormData();
        form.append("name", domainName);
        passwd = $('#deleteTrustedServerModal').find('input[name=password]').val();
        form.append("password", passwd);
        closeDeleteTrustedServerModal();
        trustedServers();
        request(API('destroyTrustRelation'), form, function(response) {
            message = JSON.parse(response)["message"];
            showSwal(message, 'success', 3000);
        }, function(error) {
            showSwal(error.message, 'error', 3000);
        });
    }

    /**
     * Creating trust relation with a new server
    */

    function showCreateTrustRelationModal(){
        $('#createTrustRelationModal').modal("show");
    }

    function createTrustRelation(){
        var form = new FormData();
        form.append("newDomainName", $('#createTrustRelationModal').find('input[name=newDomainName]').val());
        form.append("newIpAddr", $('#createTrustRelationModal').find('input[name=newIpAddr]').val());
        form.append("newType", $('#createTrustRelationModal').find('select[name=newType]').val());
        form.append("newDirection", $('#createTrustRelationModal').find('select[name=newDirection]').val());
        form.append("newCreateLocation", $('#createTrustRelationModal').find('select[name=newCreateLocation]').val());
        form.append("newUsername", $('#createTrustRelationModal').find('input[name=newUsername]').val());
        form.append("password", $('#createTrustRelationModal').find('input[name=password]').val());

        $('#createTrustRelationModal').modal("hide");
        request(API('createTrustRelation'), form, function(response) {
            message = JSON.parse(response)["message"];
            showSwal(message, 'success', 10000);
            trustedServers();
        }, function(error) {
            showSwal(error.message, 'error', 3000);
        });
    }
</script>