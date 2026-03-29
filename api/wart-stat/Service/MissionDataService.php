<?php

declare(strict_types=1);

namespace WartStat\Service;

use WartStat\Base\Database;
use WartStat\Repository\{
    MissionRepository,
    ActionRepository,
    VehicleRepository,
    MissionTypeRepository,
    BonusTypeRepository,
    MissionBonusRepository,
    SkillBonusRepository,
    ActivityTimeRepository,
    PlayTimeRepository,
    ResearchTargetRepository,
    ResearchProgressRepository,
    ActiveBoosterRepository
};
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Mission Data Service
 * Handles all mission-related operations and data aggregation
 */
class MissionDataService
{
    private Database $db;
    private LoggerInterface $logger;
    private MissionRepository $missionRepo;
    private ActionRepository $actionRepo;
    private VehicleRepository $vehicleRepo;
    private MissionTypeRepository $missionTypeRepo;
    private MissionBonusRepository $missionBonusRepo;
    private SkillBonusRepository $skillBonusRepo;
    private ActivityTimeRepository $activityTimeRepo;
    private PlayTimeRepository $playTimeRepo;
    private ResearchTargetRepository $researchTargetRepo;
    private ResearchProgressRepository $researchProgressRepo;
    private ActiveBoosterRepository $boosterRepo;

    /**
     * @param Database $db
     * @param LoggerInterface $logger
     */
    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;

        // Initialize all repositories
        $this->missionRepo = new MissionRepository($db, $logger);
        $this->actionRepo = new ActionRepository($db, $logger);
        $this->vehicleRepo = new VehicleRepository($db, $logger);
        $this->missionTypeRepo = new MissionTypeRepository($db, $logger);
        $this->missionBonusRepo = new MissionBonusRepository($db, $logger);
        $this->skillBonusRepo = new SkillBonusRepository($db, $logger);
        $this->activityTimeRepo = new ActivityTimeRepository($db, $logger);
        $this->playTimeRepo = new PlayTimeRepository($db, $logger);
        $this->researchTargetRepo = new ResearchTargetRepository($db, $logger);
        $this->researchProgressRepo = new ResearchProgressRepository($db, $logger);
        $this->boosterRepo = new ActiveBoosterRepository($db, $logger);
    }

    /**
     * Get all repositories for external access
     *
     * @return array
     */
    public function getRepositories(): array
    {
        return [
            'mission' => $this->missionRepo,
            'action' => $this->actionRepo,
            'vehicle' => $this->vehicleRepo,
            'missionType' => $this->missionTypeRepo,
            'missionBonus' => $this->missionBonusRepo,
            'skillBonus' => $this->skillBonusRepo,
            'activityTime' => $this->activityTimeRepo,
            'playTime' => $this->playTimeRepo,
            'researchTarget' => $this->researchTargetRepo,
            'researchProgress' => $this->researchProgressRepo,
            'booster' => $this->boosterRepo,
        ];
    }

    /**
     * Get mission with all related data
     *
     * @param int $missionId
     * @return array
     * @throws RuntimeException
     */
    public function getMissionComplete(int $missionId): array
    {
        $mission = $this->missionRepo->findById($missionId);
        if (!$mission) {
            throw new RuntimeException("Mission not found: {$missionId}");
        }

        return $this->enrichMissionData($mission);
    }

    /**
     * Enrich mission data with all related records
     *
     * @param array $mission
     * @return array
     */
    private function enrichMissionData(array $mission): array
    {
        $missionId = $mission['id'];

        $mission['actions'] = $this->actionRepo->findByMission($missionId);
        $mission['bonuses'] = $this->missionBonusRepo->findByMission($missionId);
        $mission['skillBonuses'] = $this->skillBonusRepo->findByMission($missionId);
        $mission['activityTimes'] = $this->activityTimeRepo->findByMission($missionId);
        $mission['playTimes'] = $this->playTimeRepo->findByMission($missionId);
        $mission['researchTarget'] = $this->researchTargetRepo->findByMission($missionId);
        $mission['researchProgress'] = $this->researchProgressRepo->findByMission($missionId);
        $mission['boosters'] = $this->boosterRepo->findByMission($missionId);

        return $mission;
    }

    /**
     * Get dashboard statistics
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        return [
            'missions' => $this->missionRepo->getStatistics(),
            'actionsByType' => $this->actionRepo->getStatisticsByType(),
            'vehicleStats' => $this->actionRepo->getVehicleStatistics(),
            'recentMissions' => $this->missionRepo->getRecentWithDetails(10),
        ];
    }

    /**
     * Get vehicle performance summary
     *
     * @return array
     */
    public function getVehiclePerformance(): array
    {
        return $this->vehicleRepo->getAllWithStats();
    }

    /**
     * Get action performance summary
     *
     * @param int $limit
     * @return array
     */
    public function getActionPerformance(int $limit = 15): array
    {
        return $this->actionRepo->getMostProfitableActions($limit);
    }

    /**
     * Check if database schema exists
     *
     * @return bool
     */
    public function schemaExists(): bool
    {
        return $this->db->schemaExists();
    }
}
