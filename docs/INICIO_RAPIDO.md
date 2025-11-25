# 🚀 Início Rápido - Guia Simples

## Opção Mais Fácil (Sem Apache) ⭐

### Passo a Passo:

1. **Tenha o PHP instalado**
   - Baixe em: https://www.php.net/downloads.php
   - Ou instale via XAMPP/WAMP (que já vem com PHP)

2. **Configure o banco de dados**
   - Abra o phpMyAdmin (se tiver XAMPP/WAMP) ou MySQL Workbench
   - Crie o banco: `petshop_db`
   - Importe o arquivo `database.sql`

3. **Configure a conexão**
   - Edite `config/database.php`
   - Ajuste usuário e senha do MySQL

4. **Inicie o servidor PHP (SEM Apache!)**
   
   Abra o PowerShell ou CMD na pasta do projeto e digite:
   ```bash
   php -S localhost:8000
   ```

5. **Acesse no navegador**
   - Site: http://localhost:8000
   - Admin: http://localhost:8000/admin/

---

## 🔧 Solução de Problemas

### "php não é reconhecido como comando"
**Solução:** Adicione o PHP ao PATH do Windows ou use o caminho completo:
```bash
C:\php\php.exe -S localhost:8000
```

### "Erro ao conectar no banco"
- Verifique se o MySQL está rodando
- Confira as credenciais em `config/database.php`
- Teste a conexão no phpMyAdmin

### "Como parar o servidor?"
- Pressione `Ctrl + C` no terminal onde está rodando

---

## 💡 Dica

O servidor PHP embutido é perfeito para desenvolvimento e testes. Para produção, você pode usar:
- Hospedagem compartilhada (geralmente já tem Apache configurado)
- Serviços como Hostinger, UOL Host, etc.
- Não precisa configurar nada manualmente!

---

**Pronto!** Você não precisa saber configurar Apache. O PHP já faz tudo! 🎉

