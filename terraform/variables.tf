variable "azure_region" {
  description = "Azure region for resources"
  type        = string
  default     = "East US"
}

variable "environment" {
  description = "Environment name (dev, staging, production)"
  type        = string
  default     = "production"
}

variable "node_count" {
  description = "Number of AKS nodes"
  type        = number
  default     = 3
}

variable "vm_size" {
  description = "VM size for AKS nodes"
  type        = string
  default     = "Standard_D2s_v3"
}

variable "mysql_admin_username" {
  description = "MySQL administrator username"
  type        = string
  sensitive   = true
}

variable "mysql_admin_password" {
  description = "MySQL administrator password"
  type        = string
  sensitive   = true
}

variable "mysql_sku" {
  description = "MySQL SKU"
  type        = string
  default     = "B_Standard_B2s"
}
