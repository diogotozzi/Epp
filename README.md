# EPP protocol for Registro.br


This PHP class aims to connect and support all EPP basic functions and extensions created by NIC.br and used in Registro.br registrar.

### How does it work?

You have to be a validated and registered company(with CNPJ) at Registro.br.

To use all functions EPP protocol provides, your company has to pass the Register.br Homologation Process(http://registro.br/provedor/epp/pt-epp-accreditation-proc.html).

The file `homologation.php` helps you to follow all the necessary steps for that ;)

### How to register a new domain?

To register a new domain, follow these simple sequences:

    //Instantiate the Epp class
    $epp = new Epp();

    //Connect with Registro.br
    $epp->connect();

    //Login with your Registro.br username and password.
    $epp->login('0001', 'YOURPASSWORD');

    //Create a new contact. This function returns the new contact ID.
    $epp->contact_create('José da Silva', 'Rua das Laranjeiras', '100', 'São Paulo', 'SP', '02127-000', 'BR', '+55.1122222222', 'test@test.com'));

    //Create a new organization.
    //Important: the organization will be pendent until a domain be vinculated to it.
    $epp->org_create('246.838.523-30', 'José da Silva', 'Rua das Figueiras', '200', 'São Paulo', 'SP', '01311-100', 'BR', '+55.1133333333', 'test@test.com', 'JOSIL44', 'José da Silva'));

    //Create a new domain and bound it to an organization.
    //Important: A ticket will be returned to track the domain creation and document check at SRF.
    $epp->domain_create('yoursite.com.br', 1, 'ns1.yoursite-idc.net', 'ns2.yoursite-idc.net', '246.838.523-30', 0));