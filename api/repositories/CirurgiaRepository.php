<?php

class cirurgiaRepository extends baseRepository
{
    public function listar(?string $data, ?string $termo, ?string $status, int $page, int $pageSize): array
	{
		$offset = ($page - 1) * $pageSize;

		$where = [];
		$params = [];

		if (!empty($data)) {
			$where[] = "c.data_cirurgia = :data";
			$params[':data'] = $data;
		}
		
		if (!empty($termo)) {
			$where[] = "(
				UPPER(p.nome_completo) LIKE UPPER(:termo)
				OR CAST(p.numero_prontuario AS VARCHAR) LIKE :termoProntuario
			)";

			$params[':termo'] = '%' . $termo . '%';
			$params[':termoProntuario'] = '%' . $termo . '%';
		}

		if (!empty($status)) {
			$where[] = "c.status = :status";
			$params[':status'] = $status;
		}

		$whereSql = '';

		if (!empty($where)) {
			$whereSql = 'WHERE ' . implode(' AND ', $where);
		}

		$sql = "
			SELECT
				c.id AS cirurgia_id,
				p.id AS paciente_id,
				p.nome_completo,
				p.data_nascimento,
				p.numero_prontuario,
				p.sexo,
				p.peso_kg,
				p.altura_cm,
				c.status,
				c.previsao_atendimento,
				c.sala,			
				c.data_cirurgia,
				e.id AS especialidade_id,
				e.descricao AS especialidade

			FROM aghu_stg.cirurgias c

			INNER JOIN aghu_stg.pacientes p
				ON p.id = c.paciente_id

			LEFT JOIN aghu_stg.especialidades e
				ON e.id = c.especialidade_id

			$whereSql

			ORDER BY 
			CASE c.status
				WHEN 'EM_PROGRESSO' THEN 0
				WHEN 'EM_PREPARO' THEN 1
				WHEN 'AGENDADA' THEN 2
				WHEN 'CONCLUIDA' THEN 3
				WHEN 'CANCELADA' THEN 4
				ELSE 5
			END,
			c.data_cirurgia,
			p.nome_completo
			LIMIT :limit
			OFFSET :offset
		";

		$stmt = $this->db->prepare($sql);

		foreach ($params as $key => $value) {
			$stmt->bindValue($key, $value);
		}

		$stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
		$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

		$stmt->execute();

		$cirurgias = $stmt->fetchAll();

		foreach ($cirurgias as &$cirurgia) {
			$alergias = $this->fetchAll("
				SELECT
					a.data_registro,
					a.descricao,
					a.motivo,
					a.criticidade_alergica,
					a.grau_certeza,
					a.manifestacao_alergica,
					a.medicamento,
					a.agente_causador

				FROM aghu_stg.paciente_alergias pa

				INNER JOIN aghu_stg.alergias a
					ON a.id = pa.alergia_id

				WHERE pa.paciente_id = :paciente

				ORDER BY a.data_registro DESC
			", [
				':paciente' => $cirurgia['paciente_id']
			]);


			$procedimentos = $this->fetchAll("
				SELECT
					p.id,
					p.codigo,
					p.descricao,
					p.cid,
					cp.principal

				FROM aghu_stg.cirurgia_procedimentos cp

				INNER JOIN aghu_stg.procedimentos p
					ON p.id = cp.procedimento_id

				WHERE cp.cirurgia_id = :cirurgia

				ORDER BY cp.principal DESC, p.descricao
			", [
				':cirurgia' => $cirurgia['cirurgia_id']
			]);

			$cirurgia['alergias'] = array_map(function ($a) {
				return [
					'data_registro' => $a['data_registro'],
					'descricao' => $a['descricao'],
					'motivo' => $a['motivo'],
					'criticidade_alergica' => $a['criticidade_alergica'],
					'grau_certeza' => $a['grau_certeza'],
					'manifestacao_alergica' => $a['manifestacao_alergica'],
					'agente_causador' => $a['agente_causador'],
					'medicamento' => [
						'descricao' => $a['medicamento']
					]
				];
			}, $alergias);

			$cirurgia['procedimentos'] = array_map(function ($p) {
				return [
					'id' => $p['id'],
					'codigo' => $p['codigo'],
					'descricao' => $p['descricao'],
					'cid' => $p['cid'],
					'principal' => (bool)$p['principal']
				];
			}, $procedimentos);
		}

		unset($cirurgia);

		$countSql = "
			SELECT COUNT(*)
			FROM aghu_stg.cirurgias c
			INNER JOIN aghu_stg.pacientes p
				ON p.id = c.paciente_id

			$whereSql
		";

		$countStmt = $this->db->prepare($countSql);

		foreach ($params as $key => $value) {
			$countStmt->bindValue($key, $value);
		}

		$countStmt->execute();

		$totalItems = (int) $countStmt->fetchColumn();

		return [
			'cirurgias' => $cirurgias,
			'totalItems' => $totalItems,
			'page' => $page,
			'pageSize' => $pageSize,
			'hasNext' => ($offset + $pageSize) < $totalItems
		];
	}

	public function listarPorIds(array $ids,?string $termo,?string $status,	int $page,int $pageSize): array
	{
			if (empty($ids)) {
				return [
					'cirurgias' => [],
					'totalItems' => 0,
					'page' => $page,
					'pageSize' => $pageSize,
					'hasNext' => false
				];
			}

			$offset = ($page - 1) * $pageSize;

			$where = [];
			$params = [];

			$idParams = [];

			foreach ($ids as $i => $id) {
				$param = ":id$i";
				$idParams[] = $param;
				$params[$param] = $id;
			}

			$where[] = "c.id IN (" . implode(',', $idParams) . ")";

			if (!empty($termo)) {
				$where[] = "(
					UPPER(p.nome_completo) LIKE UPPER(:termo)
					OR CAST(p.numero_prontuario AS VARCHAR) LIKE :termoProntuario
				)";

				$params[':termo'] = '%' . $termo . '%';
				$params[':termoProntuario'] = '%' . $termo . '%';
			}

			if (!empty($status)) {
				$where[] = "c.status = :status";
				$params[':status'] = $status;
			}

			$whereSql = 'WHERE ' . implode(' AND ', $where);

			$sql = "
				SELECT
					c.id AS cirurgia_id,
					p.id AS paciente_id,
					p.nome_completo,
					p.data_nascimento,
					p.numero_prontuario,
					p.sexo,
					p.peso_kg,
					p.altura_cm,
					c.status,
					c.previsao_atendimento,
					c.sala,
					e.id AS especialidade_id,
					e.descricao AS especialidade

				FROM aghu_stg.cirurgias c

				INNER JOIN aghu_stg.pacientes p
					ON p.id = c.paciente_id

				LEFT JOIN aghu_stg.especialidades e
					ON e.id = c.especialidade_id

				$whereSql

				ORDER BY p.nome_completo
				LIMIT :limit
				OFFSET :offset
			";

			$stmt = $this->db->prepare($sql);

			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value);
			}

			$stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
			$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

			$stmt->execute();

			$cirurgias = $stmt->fetchAll();

			foreach ($cirurgias as &$cirurgia) {

				$alergias = $this->fetchAll("
					SELECT
						a.data_registro,
						a.descricao,
						a.motivo,
						a.criticidade_alergica,
						a.grau_certeza,
						a.manifestacao_alergica,
						a.medicamento,
						a.agente_causador

					FROM aghu_stg.paciente_alergias pa

					INNER JOIN aghu_stg.alergias a
						ON a.id = pa.alergia_id

					WHERE pa.paciente_id = :paciente

					ORDER BY a.data_registro DESC
				", [
					':paciente' => $cirurgia['paciente_id']
				]);

				$procedimentos = $this->fetchAll("
					SELECT
						p.id,
						p.descricao,
						p.cid,
						cp.principal

					FROM aghu_stg.cirurgia_procedimentos cp

					INNER JOIN aghu_stg.procedimentos p
						ON p.id = cp.procedimento_id

					WHERE cp.cirurgia_id = :cirurgia

					ORDER BY cp.principal DESC, p.descricao
				", [
					':cirurgia' => $cirurgia['cirurgia_id']
				]);

				$cirurgia['alergias'] = array_map(function ($a) {
					return [
						'data_registro' => $a['data_registro'],
						'descricao' => $a['descricao'],
						'motivo' => $a['motivo'],
						'criticidade_alergica' => $a['criticidade_alergica'],
						'grau_certeza' => $a['grau_certeza'],
						'manifestacao_alergica' => $a['manifestacao_alergica'],
						'agente_causador' => $a['agente_causador'],
						'medicamento' => [
							'descricao' => $a['medicamento']
						]
					];
				}, $alergias);

				$cirurgia['procedimentos'] = array_map(function ($p) {
					return [
						'id' => $p['id'],
						'descricao' => $p['descricao'],
						'cid' => $p['cid'],
						'principal' => (bool)$p['principal']
					];
				}, $procedimentos);
			}

			unset($cirurgia);

			$countSql = "
				SELECT COUNT(*)

				FROM aghu_stg.cirurgias c

				INNER JOIN aghu_stg.pacientes p
					ON p.id = c.paciente_id

				$whereSql
			";

			$countStmt = $this->db->prepare($countSql);

			foreach ($params as $key => $value) {
				$countStmt->bindValue($key, $value);
			}

			$countStmt->execute();

			$totalItems = (int)$countStmt->fetchColumn();

			return [
				'cirurgias' => $cirurgias,
				'totalItems' => $totalItems,
				'page' => $page,
				'pageSize' => $pageSize,
				'hasNext' => ($offset + $pageSize) < $totalItems
			];
		}

    public function buscar(string $idPaciente, int $idCirurgia): ?array
    {
        $paciente = $this->fetchOne("
        SELECT
            c.id AS cirurgia_id,
            c.data_cirurgia,
            c.status,
            c.sala,

            e.id AS especialidade_id,
            e.descricao AS especialidade_descricao,

            cc.id AS centro_cirurgico_id,
            cc.descricao AS centro_cirurgico_descricao,

            p.id AS paciente_id,
            p.numero_prontuario,
            p.nome_completo,
            p.data_nascimento,
            p.sexo,
            p.peso_kg,
            p.altura_cm,

            u.codigo AS unidade_codigo,
            u.descricao AS unidade_descricao,

            lp.leito,
            lp.andar,
            lp.quarto

        FROM aghu_stg.cirurgias c

        INNER JOIN aghu_stg.pacientes p
            ON p.id = c.paciente_id

        LEFT JOIN aghu_stg.localizacao_paciente lp
            ON lp.paciente_id = p.id

        LEFT JOIN aghu_stg.unidades u
            ON u.codigo = lp.unidade_codigo

        LEFT JOIN aghu_stg.especialidades e
            ON e.id = c.especialidade_id

        LEFT JOIN aghu_stg.centros_cirurgicos cc
            ON cc.id = c.centro_cirurgico_id

        WHERE
            p.id = :paciente
        AND c.id = :cirurgia
    ", [
            ':paciente' => $idPaciente,
            ':cirurgia' => $idCirurgia
        ]);

        if ($paciente === null) {
            return null;
        }


        $alergias = $this->fetchAll("
        SELECT
            a.data_registro,
            a.descricao,
            a.motivo,
            a.criticidade_alergica,
            a.grau_certeza,
            a.manifestacao_alergica,
            a.medicamento,
            a.agente_causador

        FROM aghu_stg.paciente_alergias pa

        INNER JOIN aghu_stg.alergias a
            ON a.id = pa.alergia_id

        WHERE pa.paciente_id = :paciente

        ORDER BY a.data_registro DESC
    ", [
            ':paciente' => $idPaciente
        ]);

   
        $procedimentos = $this->fetchAll("
        SELECT
            p.id,
            p.codigo,
            p.descricao,
            p.cid,
            cp.principal

        FROM aghu_stg.cirurgia_procedimentos cp

        INNER JOIN aghu_stg.procedimentos p
            ON p.id = cp.procedimento_id

        WHERE cp.cirurgia_id = :cirurgia

        ORDER BY cp.principal DESC, p.descricao
    ", [
            ':cirurgia' => $idCirurgia
        ]);

   

        return [
            'cirurgia_id' => $paciente['cirurgia_id'],
            'paciente_id' => $paciente['paciente_id'],
            'numero_prontuario' => $paciente['numero_prontuario'],
            'nome_completo' => $paciente['nome_completo'],
			'data_cirurgia' => $paciente['data_cirurgia'],
            'data_nascimento' => $paciente['data_nascimento'],
            'sexo' => $paciente['sexo'],
            'peso_kg' => $paciente['peso_kg'],
            'altura_cm' => $paciente['altura_cm'],
			'status' => $paciente['status'],

            'localizacao_atual' => [
                'unidade' => [
                    'codigo' => $paciente['unidade_codigo'],
                    'descricao' => $paciente['unidade_descricao']
                ],
                'leito' => $paciente['leito'],
                'andar' => $paciente['andar'],
                'quarto' => $paciente['quarto']
            ],

            'alergias' => array_map(function ($a) {
                return [
                    'data_registro' => $a['data_registro'],
                    'descricao' => $a['descricao'],
                    'motivo' => $a['motivo'],
                    'criticidade_alergica' => $a['criticidade_alergica'],
                    'grau_certeza' => $a['grau_certeza'],
                    'manifestacao_alergica' => $a['manifestacao_alergica'],
                    'medicamento' => [
                        'descricao' => $a['medicamento']
                    ],
                    'agente_causador' => $a['agente_causador']
                ];
            }, $alergias),

            'cirurgias' => [
                [
                    'id' => $paciente['cirurgia_id'],
                    'data_cirurgia' => $paciente['data_cirurgia'],
                    'status_cirurgia' => $paciente['status'],
                    'especialidade' => [
                        'id' => $paciente['especialidade_id'],
                        'descricao' => $paciente['especialidade_descricao']
                    ],
                    'local' => [
                        'centro_cirurgico' => [
                            'id' => $paciente['centro_cirurgico_id'],
                            'descricao' => $paciente['centro_cirurgico_descricao']
                        ],
                        'sala' => $paciente['sala']
                    ],
                    'procedimentos' => array_map(function ($p) {
                        return [
                            'id' => $p['id'],
							'codigo' => $p['codigo'],
                            'descricao' => $p['descricao'],
                            'cid' => $p['cid'],
                            'principal' => (bool)$p['principal']
                        ];
                    }, $procedimentos)
                ]
            ]
        ];
    }
}
