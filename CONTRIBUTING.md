# Guia de Contribuição

Obrigado por contribuir para este projeto! Este guia contém instruções para garantir que todas as contribuições sejam consistentes e fáceis de revisar.

---

## **1. Nomeação de Branches**

Utilizamos um padrão específico para nomear branches. Siga estas diretrizes para facilitar a identificação e organização:

```html
<tipo>/<descrição-curta>
```

### **Tipos de Branches**

- **feature**: Para novas funcionalidades.
  - Exemplo: `feature/login-page`
- **fix**: Para correções de bugs.
  - Exemplo: `fix/button-alignment`
- **chore**: Para tarefas de manutenção ou pequenas alterações.
  - Exemplo: `chore/update-dependencies`
- **refactor**: Para reestruturação ou melhorias no código sem alterar a funcionalidade.
  - Exemplo: `refactor/auth-module`
- **test**: Para adicionar ou corrigir testes.
  - Exemplo: `test/api-endpoints`
- **hotfix**: Para correções críticas e urgentes.
  - Exemplo: `hotfix/payment-error`

---

## **2. Criação de Commits**

Os commits devem ser claros, concisos e seguir o seguinte formato:

```html
<tipo>(escopo): descrição breve
```

### **Tipos de Commits**

- **feat**: Adicionar uma nova funcionalidade.
- **fix**: Corrigir um bug.
- **chore**: Atualizar tarefas ou dependências.
- **docs**: Alterar ou adicionar documentação.
- **style**: Alterações relacionadas ao estilo de código (espaçamento, formatação, etc.).
- **refactor**: Melhorar o código sem alterar a funcionalidade.
- **test**: Adicionar ou corrigir testes.
- **perf**: Melhorias de desempenho.
- **ci**: Atualizações no pipeline de integração contínua.

### **Exemplos de Mensagens de Commit**

- `feat(auth): add user login functionality`
- `fix(ui): resolve button alignment issue`
- `chore(deps): update React to version 18`
- `docs(readme): update contribution guidelines`
- `refactor(api): optimize data fetching logic`

---

## **3. Fluxo de Trabalho**

1. **Criar uma Branch**
   - Sempre crie uma branch para suas alterações, baseada na `main` (ou outra branch de desenvolvimento primária).
   - Exemplo:

     ```bash
     git checkout -b feature/login-page
     ```

2. **Realizar Commits**
   - Faça commits frequentes e claros enquanto desenvolve.
   - Use o comando:

     ```bash
     git commit -m "feat(auth): add user authentication system"
     ```

3. **Sincronizar Alterações**
   - Sincronize suas alterações frequentemente com a branch principal para evitar conflitos:

     ```bash
     git pull origin main
     ```

4. **Abrir um Pull Request**
   - Quando terminar as alterações:
     - Suba sua branch:

       ```bash
       git push origin feature/login-page
       ```

     - Crie um Pull Request no repositório, explicando claramente as mudanças realizadas e o motivo.

---

## **4. Boas Práticas**

- **Pequenas Alterações:** Divida grandes mudanças em commits ou pull requests menores.
- **Comentários Claros:** Descreva por que você fez as mudanças, não apenas o que mudou.
- **Código Limpo:** Siga os padrões definidos no projeto para estilo e organização do código.

---

Seguindo estas diretrizes, garantimos que o processo de desenvolvimento será mais organizado e colaborativo. Obrigado por contribuir! 🚀
