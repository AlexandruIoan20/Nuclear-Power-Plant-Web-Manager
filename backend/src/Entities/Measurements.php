<?php

class Measurement {
    private string $id;
    private string $reactorId;
    private ?string $timestamp;
    private ?float $powerPercent;
    private ?float $neutronFlux;
    private ?float $reactivityPcm;
    private ?float $reactorPeriodSec;
    private ?float $tempFuelCenter;
    private ?float $tempCoolantIn;
    private ?float $tempCoolantOut;
    private ?float $tempModerator;
    private ?float $pressure;
    private ?float $flowRatePrimary;
    private ?float $flowRateSecondary;
    private ?float $steamPressure;
    private ?float $steamFlowRate;
    private ?float $feedwaterTemp;
    private ?float $radiation;
    private ?float $activityPrimary;
    private ?float $doseRateControlRoom;
    private ?float $doseRateReactorBldg;
    private ?float $airborneActivity;
    private ?float $fuelBurnupMwdT;
    private ?float $efficiency;
    private ?float $wearDelta;
    private ?float $levelReactorVessel;

    public function __construct(
        string $reactorId,
        ?string $id = null,
        ?string $timestamp = null,
        ?float $powerPercent = null,
        ?float $neutronFlux = null,
        ?float $reactivityPcm = null,
        ?float $reactorPeriodSec = null,
        ?float $tempFuelCenter = null,
        ?float $tempCoolantIn = null,
        ?float $tempCoolantOut = null,
        ?float $tempModerator = null,
        ?float $pressure = null,
        ?float $flowRatePrimary = null,
        ?float $flowRateSecondary = null,
        ?float $steamPressure = null,
        ?float $steamFlowRate = null,
        ?float $feedwaterTemp = null,
        ?float $radiation = null,
        ?float $activityPrimary = null,
        ?float $doseRateControlRoom = null,
        ?float $doseRateReactorBldg = null,
        ?float $airborneActivity = null,
        ?float $fuelBurnupMwdT = null,
        ?float $efficiency = null,
        ?float $wearDelta = null,
        ?float $levelReactorVessel = null
    ) {
        $this->id = $id ?? bin2hex(random_bytes(16));
        $this->reactorId = $reactorId;
        $this->timestamp = $timestamp ?? date('Y-m-d H:i:s');
        $this->powerPercent = $powerPercent;
        $this->neutronFlux = $neutronFlux;
        $this->reactivityPcm = $reactivityPcm;
        $this->reactorPeriodSec = $reactorPeriodSec;
        $this->tempFuelCenter = $tempFuelCenter;
        $this->tempCoolantIn = $tempCoolantIn;
        $this->tempCoolantOut = $tempCoolantOut;
        $this->tempModerator = $tempModerator;
        $this->pressure = $pressure;
        $this->flowRatePrimary = $flowRatePrimary;
        $this->flowRateSecondary = $flowRateSecondary;
        $this->steamPressure = $steamPressure;
        $this->steamFlowRate = $steamFlowRate;
        $this->feedwaterTemp = $feedwaterTemp;
        $this->radiation = $radiation;
        $this->activityPrimary = $activityPrimary;
        $this->doseRateControlRoom = $doseRateControlRoom;
        $this->doseRateReactorBldg = $doseRateReactorBldg;
        $this->airborneActivity = $airborneActivity;
        $this->fuelBurnupMwdT = $fuelBurnupMwdT;
        $this->efficiency = $efficiency;
        $this->wearDelta = $wearDelta;
        $this->levelReactorVessel = $levelReactorVessel;
    }

    public function getId(): string { return $this->id; }
    public function getReactorId(): string { return $this->reactorId; }
    public function getTimestamp(): ?string { return $this->timestamp; }
    public function getPowerPercent(): ?float { return $this->powerPercent; }
    public function getNeutronFlux(): ?float { return $this->neutronFlux; }
    public function getReactivityPcm(): ?float { return $this->reactivityPcm; }
    public function getReactorPeriodSec(): ?float { return $this->reactorPeriodSec; }
    public function getTempFuelCenter(): ?float { return $this->tempFuelCenter; }
    public function getTempCoolantIn(): ?float { return $this->tempCoolantIn; }
    public function getTempCoolantOut(): ?float { return $this->tempCoolantOut; }
    public function getTempModerator(): ?float { return $this->tempModerator; }
    public function getPressure(): ?float { return $this->pressure; }
    public function getFlowRatePrimary(): ?float { return $this->flowRatePrimary; }
    public function getFlowRateSecondary(): ?float { return $this->flowRateSecondary; }
    public function getSteamPressure(): ?float { return $this->steamPressure; }
    public function getSteamFlowRate(): ?float { return $this->steamFlowRate; }
    public function getFeedwaterTemp(): ?float { return $this->feedwaterTemp; }
    public function getRadiation(): ?float { return $this->radiation; }
    public function getActivityPrimary(): ?float { return $this->activityPrimary; }
    public function getDoseRateControlRoom(): ?float { return $this->doseRateControlRoom; }
    public function getDoseRateReactorBldg(): ?float { return $this->doseRateReactorBldg; }
    public function getAirborneActivity(): ?float { return $this->airborneActivity; }
    public function getFuelBurnupMwdT(): ?float { return $this->fuelBurnupMwdT; }
    public function getEfficiency(): ?float { return $this->efficiency; }
    public function getWearDelta(): ?float { return $this->wearDelta; }
    public function getLevelReactorVessel(): ?float { return $this->levelReactorVessel; }
    
    public function setId(string $id): void { $this->id = $id; }
    public function setReactorId(string $reactorId): void { $this->reactorId = $reactorId; }
    public function setTimestamp(?string $timestamp): void { $this->timestamp = $timestamp; }
    public function setPowerPercent(?float $powerPercent): void { $this->powerPercent = $powerPercent; }
    public function setNeutronFlux(?float $neutronFlux): void { $this->neutronFlux = $neutronFlux; }
    public function setReactivityPcm(?float $reactivityPcm): void { $this->reactivityPcm = $reactivityPcm; }
    public function setReactorPeriodSec(?float $reactorPeriodSec): void { $this->reactorPeriodSec = $reactorPeriodSec; }
    public function setTempFuelCenter(?float $tempFuelCenter): void { $this->tempFuelCenter = $tempFuelCenter; }
    public function setTempCoolantIn(?float $tempCoolantIn): void { $this->tempCoolantIn = $tempCoolantIn; }
    public function setTempCoolantOut(?float $tempCoolantOut): void { $this->tempCoolantOut = $tempCoolantOut; }
    public function setTempModerator(?float $tempModerator): void { $this->tempModerator = $tempModerator; }
    public function setPressure(?float $pressure): void { $this->pressure = $pressure; }
    public function setFlowRatePrimary(?float $flowRatePrimary): void { $this->flowRatePrimary = $flowRatePrimary; }
    public function setFlowRateSecondary(?float $flowRateSecondary): void { $this->flowRateSecondary = $flowRateSecondary; }
    public function setSteamPressure(?float $steamPressure): void { $this->steamPressure = $steamPressure; }
    public function setSteamFlowRate(?float $steamFlowRate): void { $this->steamFlowRate = $steamFlowRate; }
    public function setFeedwaterTemp(?float $feedwaterTemp): void { $this->feedwaterTemp = $feedwaterTemp; }
    public function setRadiation(?float $radiation): void { $this->radiation = $radiation; }
    public function setActivityPrimary(?float $activityPrimary): void { $this->activityPrimary = $activityPrimary; }
    public function setDoseRateControlRoom(?float $doseRateControlRoom): void { $this->doseRateControlRoom = $doseRateControlRoom; }
    public function setDoseRateReactorBldg(?float $doseRateReactorBldg): void { $this->doseRateReactorBldg = $doseRateReactorBldg; }
    public function setAirborneActivity(?float $airborneActivity): void { $this->airborneActivity = $airborneActivity; }
    public function setFuelBurnupMwdT(?float $fuelBurnupMwdT): void { $this->fuelBurnupMwdT = $fuelBurnupMwdT; }
    public function setEfficiency(?float $efficiency): void { $this->efficiency = $efficiency; }
    public function setWearDelta(?float $wearDelta): void { $this->wearDelta = $wearDelta; }
    public function setLevelReactorVessel(?float $levelReactorVessel): void { $this->levelReactorVessel = $levelReactorVessel; }
}