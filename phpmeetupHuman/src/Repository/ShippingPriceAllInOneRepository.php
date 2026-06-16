<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

class ShippingPriceAllInOneRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * It retrieves all data in an optimized way via raw SQL.
     *
     * @return array<array<string, mixed>>|null
     */
    public function getAllData(string $postalCode, string $serviceType, float $baseWeight, float $weight): ?array
    {
        $conn = $this->entityManager->getConnection();

        $sql = "
            
            SELECT 
                'location_zone' AS row_type,
                po.id AS kol_int_1,                      
                sz.id AS kol_int_2,                      
                sz.base_delivery_days AS kol_int_3,      
                po.postal_code AS kol_vchar_1,           
                po.name AS kol_vchar_2,                  
                po.currency AS kol_vchar_3,              
                sz.name AS kol_vchar_4,                  
                sz.zone_surcharge AS kol_double_1,       
                null as kol_int_4,						 
                NULL AS kol_double_2,                    
                NULL AS kol_double_3,                    
                NULL AS kol_double_4,                    
                NULL AS kol_double_5,                    
                NULL AS kol_double_6,                    
                NULL AS kol_double_7,                    
                NULL AS kol_double_8,                    
				NULL AS kol_double_9                     

            FROM post_office po
            LEFT JOIN shipping_zone sz ON po.shipping_zone_id = sz.id
            WHERE po.postal_code = :postalCode

            UNION ALL

            
            SELECT 
                'service_tariff' AS row_type,
                st.id AS kol_int_1,                      
                t.id AS kol_int_2,                       
                st.volume_divisor AS kol_int_3,          
                st.code AS kol_vchar_1,                  
                st.name AS kol_vchar_2,                  
                NULL AS kol_vchar_3,                     
                NULL AS kol_vchar_4,                     
                NUll as kol_double_1,					 
                st.reduces_estimated_delivery_days as kol_int_4,
                st.weight_surcharge AS kol_double_2,     
                st.dimensional_surcharge AS kol_double_3, 
                st.priority_multiplier AS kol_double_4,  
                st.max_weight AS kol_double_5,           
                st.max_dimension AS kol_double_6,        
                t.base_price AS kol_double_7,            
                agregirano.max_weight AS kol_double_8,   
                cijena.base_price AS kol_double_9        

            FROM service_type st
            INNER JOIN tariff t ON t.service_type_id = st.id 
                AND :baseWeight >= t.min_weight 
                AND :baseWeight < t.max_weight
            INNER JOIN (
			    SELECT t_max.service_type_id, MAX(t_max.max_weight) AS max_weight 
			    FROM tariff t_max
			    INNER JOIN service_type st_max ON t_max.service_type_id = st_max.id
			    WHERE st_max.code = :code
			    GROUP BY t_max.service_type_id
			) agregirano ON agregirano.service_type_id = st.id
            INNER JOIN (
			    SELECT t1.service_type_id, t1.base_price 
			    FROM tariff t1
			    INNER JOIN service_type st1 ON t1.service_type_id = st1.id
			    AND :weight >= t1.min_weight 
                AND :weight < t1.max_weight
			) cijena ON cijena.service_type_id = st.id
            WHERE st.code = :code;
            
        ";

        $resultSet = $conn->executeQuery($sql, [
            'postalCode' => $postalCode,
            'code' => $serviceType,
            'baseWeight' => $baseWeight,
            'weight' => $weight,
        ]);

        $data = $resultSet->fetchAllAssociative();

        return $data !== [] ? $data : null;
    }
}
