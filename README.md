# Projeto Integração de API do IBGE

Este projeto integra a API do IBGE a um site WordPress via plugin, facilitando a exibição de dados diretamente em páginas do WordPress por meio de um shortcode.

## Passos para configuração e uso

1. **Clonar o repositório**

   Clone este projeto para sua máquina local:
   ```bash
   git clone https://github.com/levigtri/integration-api-gov.git
   cd integraion-api-gov
   ```

2. **Subir o ambiente Docker**

   Execute o comando abaixo para iniciar o ambiente WordPress via Docker Compose:
   ```bash
   docker-compose up -d
   ```

3. **Acessar o WordPress**

   Abra o navegador e acesse [http://localhost](http://localhost) para finalizar a configuração inicial do WordPress (criação de usuário e senha).

4. **Empacotar o plugin**

   Compacte o arquivo `plugin.php` em um arquivo ZIP:
   ```bash
   zip plugin.zip plugin.php
   ```

5. **Instalar o plugin**

   - Acesse o painel administrativo do WordPress (`http://localhost/wp-admin`).
   - Vá em **Plugins > Adicionar novo > Enviar plugin**.
   - Faça o upload do arquivo `plugin.zip` e ative o plugin.

6. **Adicionar o shortcode à página**

   - Crie ou edite uma página no WordPress.
   - Adicione o shortcode abaixo no conteúdo:
     ```
     [ibge_pesquisa]
     ```

7. **Pronto!**

   Agora basta acessar a página onde o shortcode foi adicionado para visualizar os dados da API do IBGE e realizar os testes necessários.

---

### Observações

- Certifique-se de que o Docker e o Docker Compose estão instalados antes de iniciar.
- O plugin precisa ser instalado manualmente (upload do arquivo ZIP).
- Para dúvidas, consulte a documentação oficial do WordPress sobre [instalação de plugins](https://wordpress.org/support/article/managing-plugins/).
