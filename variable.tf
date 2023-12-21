variable "vpc-08636107c6933a988" {}

data "aws_vpc" "test-vpc" {
  id = var.vpc-08636107c6933a988
}

resource "aws_subnet" "test_subnet" {
  vpc_id            = data.aws_vpc.test-vpc.id
  availability_zone = "ap-south-1"
  cidr_block        = cidrsubnet(data.aws_vpc.test-vpc.cidr_block, 4, 1)
}
