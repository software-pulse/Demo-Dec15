terraform {
  required_providers {
    aws = {
      source = "hashicorp/aws"
      version = "5.31.0"
    }
  }
}

provider "aws" {
  region = "ap-south-1"
}

resource "aws_instance" "example" {
  ami           = "ami-0b7dc0e17972ef65f"
  instance_type = "t3a.medium"

  tags = {
    Name = "terraform sample"
}
}
