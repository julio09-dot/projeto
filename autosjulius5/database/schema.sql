-- Schema da base de dados AutosJulius (PostgreSQL)

CREATE TABLE IF NOT EXISTS usuarios (
    id       SERIAL PRIMARY KEY,
    nome     VARCHAR(100) NOT NULL,
    email    VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    senha    VARCHAR(255) NOT NULL,
    tipo     VARCHAR(10) NOT NULL DEFAULT 'vendedor',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS veiculos (
    id           SERIAL PRIMARY KEY,
    usuario_id   INT REFERENCES usuarios(id) ON DELETE SET NULL,
    marca        VARCHAR(50) NOT NULL,
    modelo       VARCHAR(50) NOT NULL,
    ano          INT NOT NULL,
    preco        DECIMAL(12, 2) NOT NULL CHECK (preco >= 0),
    quilometros  INT CHECK (quilometros IS NULL OR quilometros >= 0),
    combustivel  VARCHAR(20),
    cambio       VARCHAR(20),
    pecas        TEXT,
    descricao    TEXT,
    imagem       VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Migração para bases já criadas antes da coluna imagem existir
ALTER TABLE veiculos ADD COLUMN IF NOT EXISTS imagem VARCHAR(255);

-- Primeiro administrador (senha: admin123 — troque após o primeiro login)
INSERT INTO usuarios (nome, email, senha, tipo)
VALUES (
    'Administrador',
    'admin@autosjulius.com',
    '$2y$12$zFTPRGZi0fuFmu70OVb4FOkIoIm1jmybD5KZt.vfhHQkhHxPCEsUu',
    'admin'
) ON CONFLICT (email) DO NOTHING;

-- 3 veículos de exemplo (aparecem no início)
INSERT INTO veiculos (usuario_id, marca, modelo, ano, preco, quilometros, combustivel, cambio, pecas, descricao)
SELECT (SELECT id FROM usuarios WHERE email = 'admin@autosjulius.com'), v.marca, v.modelo, v.ano, v.preco, v.quilometros, v.combustivel, v.cambio, v.pecas, v.descricao
FROM (VALUES
    ('Toyota', 'Corolla 2.0 XEi', 2023, 129900.00, 28000, 'Flex', 'Automático',
     'Revisões em concessionária todas as 10.000 km; pastilhas de freio trocadas aos 20.000 km; pneus originais em bom estado.',
     'Único dono, IPVA pago, multimídia Apple CarPlay/Android Auto, câmera de ré.'),
    ('Jeep', 'Compass 1.3 Turbo', 2022, 139900.00, 42000, 'Flex', 'Automático',
     'Troca de óleo e filtros feita aos 40.000 km; embreagem original sem sinais de desgaste.',
     'Versão longitude, teto solar, sensor de estacionamento, 4 portas, baixa quilometragem.'),
    ('Honda', 'Civic 2.0 Touring', 2020, 119900.00, 50000, 'Gasolina', 'CVT',
     'Revisão dos 50.000 km completa; correia e correia dentada conferidas; freios revisados.',
     'Teto solar elétrico, bancos em couro, faróis LED, teto solar elétrico, multimídia completa.')
) AS v(marca, modelo, ano, preco, quilometros, combustivel, cambio, pecas, descricao)
WHERE NOT EXISTS (SELECT 1 FROM veiculos);
