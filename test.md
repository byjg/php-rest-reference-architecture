## AI Documentation Restructuring Prompt (Reusable)

Restructure this project’s documentation into a professional, scalable, software-style documentation system.

### Context
- The project currently contains:
    - `README.md`
    - `docs/` directory
- During CI/CD, all documentation files are merged into a centralized documentation site.
- In the final published output:
    - `README.md` becomes the **Home / Index page**
    - All files from `docs/` exist at the same documentation level as the README


### Objective

Reorganize the documentation to follow a modern, developer-focused documentation architecture similar to professional CLI and package-management tools that:
- Clearly separate onboarding from deep technical details
- Prioritize task-based learning before conceptual depth
- Use progressive disclosure (simple → advanced)
- Maintain strict separation between explanation and reference
- Organize content in a structured, left-navigation hierarchy
- Avoid mixing tutorials with parameter definitions
- Scale cleanly as the project grows
- Use Docusaurus MD style


### Target Documentation Model

Organize content into logical sections such as:

#### 1. Getting Started
- Installation
- Quick start
- Minimal working example
- First successful run
- Basic configuration


Purpose: Help users succeed quickly.

#### 2. Guides
- Task-oriented documentation
- Step-by-step workflows
- Practical use cases
- Common operational scenarios
- Real-world examples


Purpose: Show how to accomplish specific goals.

#### 3. Concepts
- Architecture
- Internal design
- Execution flow
- System components
- Trade-offs and decisions
- Mental models for understanding how it works


Purpose: Explain _why_ and _how_ the system works.

#### 4. Reference
- CLI arguments
- Configuration parameters
- Environment variables
- File structure
- API definitions
- Schemas
- Internal modules
- Edge cases and limits


Purpose: Provide precise, structured, technical lookup documentation.

---

### Rules
- The README must act as a structured entry point, not a content dump.
- Do not mix:
    - Tutorials with parameter definitions
    - Concept explanations with procedural guides
- Prefer multiple focused documents over long monolithic pages.
- Ensure naming consistency and predictable hierarchy.
- Sections are optional — not all projects require all categories.
- Additional sections may be added when justified (e.g., Advanced Topics, Integrations, FAQ, Contributing).

---

### Expected Output

Produce:
1. A proposed new documentation structure.
2. A content reorganization plan mapping existing files into the new structure.
3. Suggested headings and hierarchy.
4. Structural improvements to clarity and scalability.

The final result should resemble high-quality developer documentation used by modern CLI tools and packaging systems: clean onboarding flow, strict information architecture, and clear separation between learning paths and technical reference.