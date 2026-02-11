terraform {
  required_version = ">= 1.0"

  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "~> 3.0"
    }
  }

  backend "azurerm" {
    resource_group_name  = "ums-terraform-rg"
    storage_account_name = "umsterraformstate"
    container_name       = "tfstate"
    key                  = "ums.terraform.tfstate"
  }
}

provider "azurerm" {
  features {}
}

# Resource Group
resource "azurerm_resource_group" "ums" {
  name     = "ums-${var.environment}-rg"
  location = var.azure_region

  tags = {
    Environment = var.environment
    Project     = "UMS"
    ManagedBy   = "Terraform"
  }
}

# Azure Kubernetes Service
resource "azurerm_kubernetes_cluster" "ums" {
  name                = "ums-${var.environment}-aks"
  location            = azurerm_resource_group.ums.location
  resource_group_name = azurerm_resource_group.ums.name
  dns_prefix          = "ums-${var.environment}"

  default_node_pool {
    name       = "default"
    node_count = var.node_count
    vm_size    = var.vm_size
  }

  identity {
    type = "SystemAssigned"
  }

  network_profile {
    network_plugin    = "azure"
    load_balancer_sku = "standard"
  }

  tags = {
    Environment = var.environment
    Project     = "UMS"
  }
}

# Azure Database for MySQL Flexible Server
resource "azurerm_mysql_flexible_server" "ums" {
  name                   = "ums-${var.environment}-mysql"
  resource_group_name    = azurerm_resource_group.ums.name
  location               = azurerm_resource_group.ums.location
  administrator_login    = var.mysql_admin_username
  administrator_password = var.mysql_admin_password
  sku_name               = var.mysql_sku
  version                = "8.0.21"

  storage {
    size_gb = 20
  }

  backup_retention_days        = 7
  geo_redundant_backup_enabled = false

  tags = {
    Environment = var.environment
    Project     = "UMS"
  }
}

resource "azurerm_mysql_flexible_database" "ums" {
  name                = "ums_db"
  resource_group_name = azurerm_resource_group.ums.name
  server_name         = azurerm_mysql_flexible_server.ums.name
  charset             = "utf8mb4"
  collation           = "utf8mb4_unicode_ci"
}

# Azure Cache for Redis
resource "azurerm_redis_cache" "ums" {
  name                = "ums-${var.environment}-redis"
  location            = azurerm_resource_group.ums.location
  resource_group_name = azurerm_resource_group.ums.name
  capacity            = 1
  family              = "C"
  sku_name            = "Standard"
  enable_non_ssl_port = false
  minimum_tls_version = "1.2"

  redis_configuration {
    maxmemory_policy = "allkeys-lru"
  }

  tags = {
    Environment = var.environment
    Project     = "UMS"
  }
}

# Azure Storage Account (for backups and file storage)
resource "azurerm_storage_account" "ums" {
  name                     = "ums${var.environment}storage"
  resource_group_name      = azurerm_resource_group.ums.name
  location                 = azurerm_resource_group.ums.location
  account_tier             = "Standard"
  account_replication_type = "LRS"
  min_tls_version          = "TLS1_2"

  tags = {
    Environment = var.environment
    Project     = "UMS"
  }
}

# Outputs
output "kube_config" {
  value     = azurerm_kubernetes_cluster.ums.kube_config_raw
  sensitive = true
}

output "mysql_fqdn" {
  value = azurerm_mysql_flexible_server.ums.fqdn
}

output "redis_hostname" {
  value = azurerm_redis_cache.ums.hostname
}

output "redis_primary_connection_string" {
  value     = azurerm_redis_cache.ums.primary_connection_string
  sensitive = true
}

output "storage_account_name" {
  value = azurerm_storage_account.ums.name
}
