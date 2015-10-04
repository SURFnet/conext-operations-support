# -*- ruby -*-
# Copyright 2015 SURFnet B.V.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
Vagrant.configure("2") do |config|
    config.vm.provider :virtualbox do |vm|
        vm.name = "Conext Operations Support"
        vm.memory = 1024
    end

    config.vm.box = "jayunit100/centos7"

    config.vm.hostname = "ops-dev.engine.coin.surf.net"
    config.vm.network :private_network, ip: "192.168.67.10"

    config.hostsupdater.aliases = [
            config.vm.hostname
     ]

    config.vm.provision "ansible" do |ansible|
        ansible.playbook = "ansible/development.yml"
        ansible.inventory_path = "ansible/inventories/development"
        ansible.limit = "all"
    end

    config.vm.synced_folder "./", "/var/www/" + config.vm.hostname, type: "nfs"
end
